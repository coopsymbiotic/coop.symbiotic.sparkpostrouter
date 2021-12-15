<?php

return [
  'sparkpostrouter_ignore_subaccounts' => [
    'group_name' => 'SparkPost Router Settings',
    'group' => 'sparkpostrouter',
    'name' => 'sparkpostrouter_ignore_subaccounts',
    'type' => 'Text',
    'quick_form_type' => 'Element',
    'html_type' => 'Text',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Ignore Sub-Accounts',
    'description' => 'Comma-separated list of sub-accounts to ignore.',
    'help_text' => '',
    'settings_pages' => [
      'sparkpostrouter' => [
        'weight' => 20,
      ],
    ],
  ],
];
