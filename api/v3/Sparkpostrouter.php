<?php

/**
 * Route the messages to their final destination.
 * Implements Sparkpostrouter.process_messages
 *
 * @param  array  input parameters
 *
 * @return array API Result Array
 * @static void
 * @access public
 */
function civicrm_api3_sparkpostrouter_process_messages($params) {
  $processed = 0;
  $errors = 0;

  $client = new GuzzleHttp\Client();
  $custom_table_name = CRM_Core_DAO::singleValueQuery('SELECT table_name FROM civicrm_custom_group WHERE name = "Sparkpost_Router"');
  $ignore_subaccounts = Civi::settings()->get('sparkpostrouter_ignore_subaccounts');
  $dao = NULL;

  if ($ignore_subaccounts) {
    $ignore_subaccounts = explode(',', $ignore_subaccounts);
  }

  // Allow force-replay of a specific message if the ID is provided.
  if (!empty($params['id'])) {
    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_sparkpost_router WHERE id = %1', [
      1 => [$params['id'], 'Positive'],
    ]);
  }
  else {
    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_sparkpost_router WHERE relay_status = 0');
  }

  while ($dao->fetch()) {
    $event = json_decode($dao->data);
    $friendly_from = $event->friendly_from;
    $sender_domain = explode('@', $friendly_from)[1];
    $subaddress = '';
    $webhook_url = NULL;

    if (!in_array($event->type, ['bounce', 'spam_complaint', 'policy_rejection', 'open', 'click'])) {
      // FIXME:
      // - document statuses? (ex: 3 = ignored)
      CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 3, relay_date = NOW() WHERE id = %1', [
        1 => [$dao->id, 'Positive'],
      ]);
      $processed++;
      continue;
    }

    // Check for a subaddress
    // friendly_from
    if (preg_match('/^[-_0-9a-zA-Z]+\+([0-9a-zA-Z]+)@/', $event->friendly_from, $matches)) {
      $subaddress = $matches[1];
    }
    elseif (preg_match('/^no-reply-([0-9a-zA-Z]+)@/', $event->friendly_from, $matches)) {
      $subaddress = $matches[1];
    }
    elseif (preg_match('/^sansreponse-([0-9a-zA-Z]+)@/', $event->friendly_from, $matches)) {
      $subaddress = $matches[1];
    }
    elseif (preg_match('/^sans-reponse-([0-9a-zA-Z]+)@/', $event->friendly_from, $matches)) {
      $subaddress = $matches[1];
    }

    // Lookup subaccount, it can be zero
    if (isset($event->subaccount_id)) {
      if (in_array($event->subaccount_id, $ignore_subaccounts)) {
        // @todo Document statuses? (3 = ignored)
        CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 3, relay_date = NOW() WHERE id = %1', [
          1 => [$dao->id, 'Positive'],
        ]);
        continue;
      }

      if ($subaddress) {
        $webhook_url = CRM_Core_DAO::singleValueQuery('SELECT sparkpost_webhook_url FROM ' . $custom_table_name . ' WHERE sparkpost_subaccount = %1 AND subaddress = %2', [
          1 => [$event->subaccount_id, 'Integer'],
          2 => [$subaddress, 'String'],
        ]);
      }
      else {
        $webhook_url = CRM_Core_DAO::singleValueQuery('SELECT sparkpost_webhook_url FROM ' . $custom_table_name . ' WHERE sparkpost_subaccount = %1 AND (subaddress = "" OR subaddress is NULL)', [
          1 => [$event->subaccount_id, 'Integer'],
        ]);
      }
    }

    // Lookup by sender domain, if subaccount not found
    // We do not support a subaddress here, because this kind of check is legacy-only.
    if (empty($webhook_url)) {
      // FIXME: this isn't ideal, could cause problems if: fooacme.org and acme.org
      // Then again, that's why we use subaccounts, so this is just temporary?
      $webhook_url = CRM_Core_DAO::singleValueQuery('SELECT sparkpost_webhook_url FROM ' . $custom_table_name . ' WHERE sparkpost_domains LIKE %1 AND (subaddress = "" OR subaddress IS NULL)', [
        1 => ['%' . $sender_domain . '%', 'String'],
      ]);
    }

    if (!$webhook_url) {
      throw new Exception("SparkpostRouter: webhook not found (check the contact record for that client): subaccount_id {$event->subaccount_id}, domain: {$sender_domain}, subaddress: {$subaddress}.");
    }

    $obj = new stdClass();
    $obj->msys = new stdClass();
    $obj->msys->message_event = json_decode($dao->data);

    $data = [
      0 => $obj,
    ];

    try {
      $response = $client->post($webhook_url, [
        'json' => $data,
        'allow_redirects' => false,
      ]);

      $code = $response->getStatusCode();

      if ($code == 200) {
        CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 1, relay_date = NOW() WHERE id = %1', [
          1 => [$dao->id, 'Positive'],
        ]);
        $processed++;
      }
      else {
        Civi::log()->error('SparkpostRouter: error processing message, invalid response code. Make sure it is not redirecting.', [
          'error' => 'Received http response ' . $code,
          'webhook' => $webhook_url,
          'data' => $data,
        ]);

        throw new Exception("SparkpostRouter: error processing message to webhook: $webhook_url : invalid http response code ($code). Make sure it is not redirecting.");
      }
    }
    catch (Exception $e) {
      Civi::log()->error('SparkpostRouter: error processing message', [
        'error' => $e->getMessage(),
        'webhook' => $webhook_url,
        'data' => $data,
      ]);

      throw new Exception('SparkpostRouter: error processing message to webhook: ' . $webhook_url . ': ' . $e->getMessage());
    }
  }

  $values = [
    'processed' => $processed,
    'errors' => $errors,
  ];

  return civicrm_api3_create_success($values, $params, 'Job', 'process_messages');
}
