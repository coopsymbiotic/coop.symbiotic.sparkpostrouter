<?php

require_once 'sparkpostrouter.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function sparkpostrouter_civicrm_config(&$config) {
  _sparkpostrouter_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function sparkpostrouter_civicrm_xmlMenu(&$files) {
  _sparkpostrouter_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function sparkpostrouter_civicrm_install() {
  _sparkpostrouter_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function sparkpostrouter_civicrm_uninstall() {
  _sparkpostrouter_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function sparkpostrouter_civicrm_enable() {
  _sparkpostrouter_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function sparkpostrouter_civicrm_disable() {
  _sparkpostrouter_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function sparkpostrouter_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _sparkpostrouter_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function sparkpostrouter_civicrm_managed(&$entities) {
  _sparkpostrouter_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sparkpostrouter_civicrm_caseTypes(&$caseTypes) {
  _sparkpostrouter_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function sparkpostrouter_civicrm_angularModules(&$angularModules) {
_sparkpostrouter_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function sparkpostrouter_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _sparkpostrouter_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_check().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_check
 */
function sparkpostrouter_civicrm_check(&$messages) {

  $cnt = 0;
  $latest_unprocessed = ts('-none-');
  $unprocessed_in_hours = 0;

  // unprocessed count
  $dao = CRM_Core_DAO::executeQuery('SELECT count(*) as cnt FROM civicrm_sparkpost_router WHERE relay_status = 0');
  if ($dao->fetch()) {
    $cnt = $dao->cnt;
  }

  // older unprocessed
  $dao = CRM_Core_DAO::executeQuery("
SELECT -TIMESTAMPDIFF(HOUR, NOW(), received_date) AS hours, received_date latest_date
FROM civicrm_sparkpost_router
WHERE relay_status = 0
ORDER BY received_date LIMIT 1");
  if ($dao->fetch()) {
    $latest_unprocessed = $dao->latest_date;
    $unprocessed_in_hours = $dao->hours;
  }

  // latest received
  $dao = CRM_Core_DAO::executeQuery('SELECT -TIMESTAMPDIFF(DAY, NOW(), received_date) AS days, received_date latest_date FROM civicrm_sparkpost_router ORDER BY received_date DESC LIMIT 1');
  if ($dao->fetch()) {
    $latest_in_days = $dao->days;
  }

  // ensure the queue has been run in a reasonnable time and that there is a recent event (i.e. sparkpost still send us messages)
  if ($unprocessed_in_hours < 3 && $latest_in_days <= 2) {
    $status = \Psr\Log\LogLevel::INFO;
  } elseif ($unprocessed_in_hours < 6 && $latest_in_days <= 4) {
    $status = \Psr\Log\LogLevel::WARNING;
  } else {
    $status = \Psr\Log\LogLevel::ERROR;
  }

  $messages[] = new CRM_Utils_Check_Message(
   'sparkpostrouter',
   ts('Items waiting to be processed : %1. Date of the oldest unprocessed event : %2. Number of days since latest event : %3', array(1 => $cnt, 2 => $latest_unprocessed, 3 => $latest_in_days)),
   ts('Sparkpost Router'),
   $status,
   'fa-tasks'
  );

}

