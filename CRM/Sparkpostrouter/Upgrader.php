<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Sparkpostrouter_Upgrader extends CRM_Sparkpostrouter_Upgrader_Base {

  /**
   * Run an external SQL script when the module is installed.
   */
  public function install() {
    $this->executeSqlFile('sql/install.sql');
  }

  /**
   * Run an external SQL script when the module is uninstalled.
   */
  public function uninstall() {
  }

  /**
   *
   */
  public function enable() {
  }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  public function disable() {
  }

  /**
   * Add support for a shared domain name.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');

    $customGroup = \Civi\Api4\CustomGroup::get(false)
      ->addWhere('name', '=', 'Sparkpost_Router')
      ->execute()
      ->first();

    if (!empty($customGroup)) {
      $results = \Civi\Api4\CustomField::create(false)
        ->addValue('custom_group_id:name', 'Sparkpost_Router')
        ->addValue('label', 'From Subaddress')
        ->addValue('name', 'subaddress')
        ->addValue('column_name', 'subaddress')
        ->addValue('html_type', 'Text')
        ->addValue('data_type', 'String')
        ->addValue('help_post', "Shared From domain (ex: generic email notification domain) can use a subaddress (ex: noreply+sitename@example.org) to route bounces. IMPORTANT: You must still set the Sparkpost subaccount.")
        ->addValue('is_searchable', TRUE)
        ->addValue('is_search_range', FALSE)
        ->execute();
    }

    return TRUE;
  }

}
