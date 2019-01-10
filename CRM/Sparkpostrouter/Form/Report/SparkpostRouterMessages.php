<?php

class CRM_Sparkpostrouter_Form_Report_SparkpostRouterMessages extends CRM_Report_Form {
  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE;

  protected $_params = [];

  public function __construct() {
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;

    parent::__construct();

    $this->_columns = [];

    $this->_columns['civicrm_sparkpost_router'] = [
      'alias' => 'spm',
      'fields' => [
        'id' => [
          'title' => ts('Id'),
          'type' => CRM_Utils_Type::T_INT,
          'default' => TRUE,
          'required' => TRUE,
        ],
        'type' => [
          'title' => ts('Type'),
          'type' => CRM_Utils_Type::T_STRING,
          'default' => TRUE,
          'required' => TRUE,
        ],
        'received_date' => [
          'title' => ts('Received Date'),
          'type' => CRM_Utils_Type::T_TIME,
          'default' => TRUE,
          'required' => TRUE,
        ],
        'relay_date' => [
          'title' => ts('Relay Date'),
          'type' => CRM_Utils_Type::T_TIME,
          'default' => TRUE,
          'required' => TRUE,
        ],
        'relay_status' => [
          'title' => ts('Relay Status'),
          'type' => CRM_Utils_Type::T_INT,
          'default' => TRUE,
          'required' => TRUE,
        ],
        'customer_id' => [
          'title' => ts('Customer ID'),
          'type' => CRM_Utils_Type::T_INT,
          'required' => TRUE,
        ],
        'subaccount_id' => [
          'title' => ts('Subaccount ID'),
          'type' => CRM_Utils_Type::T_INT,
          'required' => TRUE,
        ],
        'message_id' => [
          'title' => ts('Message ID'),
          'type' => CRM_Utils_Type::T_STRING,
          // 'required' => TRUE,
        ],
        'subject' => [
          'title' => ts('Subject'),
          'type' => CRM_Utils_Type::T_STRING,
          'required' => TRUE,
        ],
        'data' => [
          'title' => ts('Data'),
          'type' => CRM_Utils_Type::T_STRING,
          'required' => TRUE,
        ],
      ],
      'filters' => array(
        'subaccount_id' => array(
          'title' => ts('Subaccount ID'),
          'operatorType' => CRM_Report_Form::OP_INT,
          'type' => CRM_Utils_Type::T_INT,
        ),
        'type' => array(
          'title' => ts('Type'),
          'operatorType' => CRM_Report_Form::OP_STRING,
          'type' => CRM_Utils_Type::T_STRING,
        ),
        'received_date' => array(
          'title' => ts('Received Date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'relay_date' => array(
          'title' => ts('Relayed Date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'relay_status' => array(
          'title' => ts('Relayed Status'),
          'type' => CRM_Utils_Type::T_INT,
          'operatorType' => CRM_Report_Form::OP_MULTISELECT,
          'options' => [
            0 => 'Pending',
            1 => 'Delivered',
            2 => 'Failed',
            3 => 'Ignored',
          ],
        ),
        'data' => array(
          'title' => ts('Data'),
          'operatorType' => CRM_Report_Form::OP_STRING,
          'type' => CRM_Utils_Type::T_STRING,
        ),
      ),
    ];
  }

  function preProcess() {
    $this->assign('reportTitle', ts("SparkPost Router Messages"));

    parent::preProcess();
  }

  /**
   * Generic select function.
   * Most reports who declare columns implicitely will call this and also define more columnHeaders.
   */
  function select() {
/*
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) || CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }
*/

    parent::select();

    // Remove the "subject" from the query
    // FIXME: there is a cleaner way of doing this.
    $this->_select = preg_replace('/, spm_civireport.subject as civicrm_sparkpost_router_subject/', '', $this->_select);
  }

  function from() {
    $this->_from = 'FROM civicrm_sparkpost_router as spm_civireport';
  }

  /**
   * This is only to apply the date filters. It is the template code from CiviCRM reports.
   * Child reports are expected to apply their own filters on the query as well.
   */
  function where() {
    // This should not be necessary, but for some reason
    // the where clauses do not get generated otherwise.
    $this->setParams($this->controller->exportValues($this->_name));

    parent::where();
  }

  public function limit($rowCount = self::ROW_COUNT_LIMIT) {
    $this->_limit = 'LIMIT 500';
  }

  public function alterDisplay(&$rows) {
    foreach ($rows as &$row) {
      $data = json_decode($row['civicrm_sparkpost_router_data']);
      $row['civicrm_sparkpost_router_subject'] = $data->subject;
    }
  }

}
