<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed/
return [
  0 => [
    'name' => 'CRM_Sparkpostrouter_Form_Report_SparkpostRouterMessages',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Sparkpost Router Messages',
      'description' => 'Sparkpost Router Messages',
      'class_name' => 'CRM_Sparkpostrouter_Form_Report_SparkpostRouterMessages',
      'report_url' => 'sparkpost-messages',
      'component' => 'CiviMail',
    ],
  ],
];
