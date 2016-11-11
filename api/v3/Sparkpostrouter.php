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

  require 'vendor/autoload.php';
  $client = new GuzzleHttp\Client();

  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_sparkpost_router WHERE relay_status = 0');

  // FIXME: we should use subaccounts for everything
  // this way the 'subaccount_id' would always be available?
  $map_clients = [
    'symbiotic.coop' => 'https://crm.symbiotic.coop/fr/civicrm/sparkpost/callback',
  ];

  while ($dao->fetch()) {
    $event = json_decode($dao->data);
    $friendly_from = $event->friendly_from;
    $sender_domain = explode('@', $friendly_from)[1];
    $webhook_url = NULL;

    if (!in_array($event->type, ['bounce', 'spam_complaint', 'policy_rejection'])) {
      // FIXME:
      // - move to BAO
      // - document statuses? (ex: 3 = ignored)
      CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 3, relay_date = NOW() WHERE id = %1', [
        1 => [$dao->id, 'Positive'],
      ]);
      $processed++;
      continue;
    }

    if (isset($map_clients[$sender_domain])) {
      $webhook_url = $map_clients[$sender_domain];
    }
    else {
      Civi::log()->warning(ts("Could not find webhook for sender: %1", [1=>$sender_domain]));
      // FIXME:
      // - move to BAO
      // - log a more explicit error?
      CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 2, relay_date = NOW() WHERE id = %1', [
        1 => [$dao->id, 'Positive'],
      ]);
      $errors++;
      continue;
    }

    $obj = new stdClass();
    $obj->msys = new stdClass();
    $obj->msys->message_event = json_decode($dao->data);

    $data = [
      0 => $obj,
    ];

    $response = $client->post($webhook_url, [
      'body' => json_encode($data),
    ]);

    $code = $response->getStatusCode();

    if ($code == 200) {
      CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 1, relay_date = NOW() WHERE id = %1', [
        1 => [$dao->id, 'Positive'],
      ]);
      $processed++;
    }
    else {
      // FIXME:
      // - move to BAO
      // - log a more explicit error?
      CRM_Core_DAO::executeQuery('UPDATE civicrm_sparkpost_router SET relay_status = 2, relay_date = NOW() WHERE id = %1', [
        1 => [$dao->id, 'Positive'],
      ]);
      $errors++;
    }
  }

  $values = [
    'processed' => $processed,
    'errors' => $errors,
  ];

  return civicrm_api3_create_success($values, $params, 'Job', 'process_messages');
}
