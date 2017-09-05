<?php

return [
  'sparkpostrouter_subaccount_field' => [
    'group_name' => 'SparkPost Router Settings',
    'group' => 'sparkpostrouter',
    'name' => 'sparkpostrouter_subaccount_field',
    'type' => 'Text',
    'quick_form_type' => 'Element',
    'html_type' => 'Select',
    'pseudoconstant' => array(
      'callback' => 'CRM_Core_I18n::languages',
    ),
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Custom field with the subaccount ID',
    'description' => '',
    'help_text' => '',
  ],
  'sparkpostrouter_domain_field' => [
    'group_name' => 'SparkPost Router Settings',
    'group' => 'sparkpostrouter',
    'name' => 'sparkpostrouter_domain_field',
    'type' => 'Text',
    'quick_form_type' => 'Element',
    'html_type' => 'Select',
    'pseudoconstant' => array(
      'callback' => 'CRM_Core_I18n::languages',
    ),
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Custom field with the permitted domains',
    'description' => '',
    'help_text' => '',
  ],
];
