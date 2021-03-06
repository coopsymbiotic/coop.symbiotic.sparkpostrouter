<?php

class CRM_Sparkpostrouter_Form_Settings extends CRM_Admin_Form_Setting {
  protected $_settingFilter = [
    'group' => 'sparkpostrouter',
  ];

  protected $_settings = [
    'sparkpostrouter_subaccount_field' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'sparkpostrouter_domain_field' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];

  public function buildQuickForm() {
    parent::buildQuickForm();
  }
}
