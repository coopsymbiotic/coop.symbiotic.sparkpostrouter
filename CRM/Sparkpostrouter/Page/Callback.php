<?php

class CRM_Sparkpostrouter_Page_Callback extends CRM_Core_Page {

  public function run() {
    // Based on:
    // https://github.com/cividesk/com.cividesk.email.sparkpost/blob/master/CRM/Sparkpost/Page/callback.php

    // The $_POST variable does not work because this is json data
    $postdata = file_get_contents('php://input');
    $elements = json_decode($postdata);

    foreach ($elements as $element) {
      if (! ($element->msys && $element->msys->message_event)) {
        Civi::log()->warning("SparkPost router ignored an unhandled event: " . print_r($element, 1));
        continue;
      }

      // Example event for 'delivery':
      // [delv_method] => esmtp
      // [rcpt_to] => mathieu@symbiotic.coop
      // [type] => delivery
      // [timestamp] => 1475699173
      // [rcpt_tags] => Array ()
      // [msg_size] => 1347
      // [rcpt_meta] => stdClass Object ()
      // [message_id] => 0003e361f5572c1815bf
      // [customer_id] => 18121
      // [transactional] => 1
      // [subject] => Test for SparkPost settings
      // [sending_ip] => 52.38.191.228
      // [num_retries] => 0
      // [friendly_from] => info@symbiotic.coop
      // [queue_time] => 1923
      // [ip_pool] => ferme
      // [template_id] => template_66422098890361561
      // [template_version] => 0
      // [routing_domain] => symbiotic.coop
      // [msg_from] => msprvs1=1234567gYdCvc=bounces-18121@sparkpostmail.com
      // [ip_address] => 192.171.60.58
      // [event_id] => 66422098897727196
      // [transmission_id] => 66422098890361561
      // [raw_rcpt_to] => bob@example.org

      $event = $element->msys->message_event;
      $subaccount_id = (isset($event->subaccount_id) ? $event->subaccount_id : 0);

      CRM_Core_DAO::executeQuery('
        INSERT INTO civicrm_sparkpost_router(type, received_date, customer_id, subaccount_id, message_id, data)
        VALUES (%1, %2, %3, %4, %5, %6)', [
          1 => [$event->type, 'String'],
          2 => [date('YmdHis'), 'Timestamp'],
          3 => [$event->customer_id, 'Positive'],
          4 => [$subaccount_id, 'Integer'],
          5 => [$event->message_id, 'String'],
          6 => [json_encode($event), 'String'],
      ]);
    }

    CRM_Utils_System::civiExit();
  }
}
