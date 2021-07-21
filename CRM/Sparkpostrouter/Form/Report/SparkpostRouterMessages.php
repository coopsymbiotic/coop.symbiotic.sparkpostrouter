<?php

class CRM_Sparkpostrouter_Form_Report_SparkpostRouterMessages extends CRM_Report_Form {
  protected $_summary = NULL;
  protected $_customGroupExtends = [];
  protected $_customGroupGroupBy = FALSE;

  public function __construct() {
    parent::__construct();

    // Reset columns
    $this->_columns = [];

    // All fields are required, so we must set this otherwise $this->_params
    // will not be populated
    $this->_noFields = TRUE;

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
          'pseudofield' => TRUE,
        ],
        'email' => [
          'title' => ts('Email'),
          'type' => CRM_Utils_Type::T_STRING,
          'required' => TRUE,
          'pseudofield' => TRUE,
        ],
        'reason' => [
          'title' => ts('Reason'),
          'type' => CRM_Utils_Type::T_STRING,
          'required' => TRUE,
          'pseudofield' => TRUE,
        ],
        'data' => [
          'title' => ts('Data'),
          'type' => CRM_Utils_Type::T_STRING,
          'required' => TRUE,
        ],
      ],
      'filters' => [
        'subaccount_id' => [
          'title' => ts('Subaccount ID'),
          'operatorType' => CRM_Report_Form::OP_INT,
          'type' => CRM_Utils_Type::T_INT,
        ],
        'type' => [
          'title' => ts('Type'),
          'operatorType' => CRM_Report_Form::OP_MULTISELECT,
          'options' => ['delay' => ts('Delay'), 'bounce' => ts('Bounce'), 'spam_complaint' => ts('Spam complaint'), 'out_of_band' => ts('Out of band'), 'policy_rejection' => ts('Policy rejection')],
          'type' => CRM_Utils_Type::T_STRING,
        ],
        'received_date' => [
          'title' => ts('Received Date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ],
        'relay_date' => [
          'title' => ts('Relayed Date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ],
        'relay_status' => [
          'title' => ts('Relayed Status'),
          'type' => CRM_Utils_Type::T_INT,
          'operatorType' => CRM_Report_Form::OP_MULTISELECT,
          'options' => [
            0 => 'Pending',
            1 => 'Delivered',
            2 => 'Failed',
            3 => 'Ignored',
          ],
        ],
        'data' => [
          'title' => ts('Data'),
          'operatorType' => CRM_Report_Form::OP_STRING,
          'type' => CRM_Utils_Type::T_STRING,
        ],
      ],
    ];
  }

  function preProcess() {
    $this->assign('reportTitle', ts("SparkPost Router Messages"));
    parent::preProcess();
  }

  function from() {
    $this->_from = 'FROM civicrm_sparkpost_router as spm_civireport';
  }

  public function limit($rowCount = self::ROW_COUNT_LIMIT) {
    if ($rowCount) {
      // $this->_limit = 'LIMIT ' . $rowCount;
    }
  }

  public function postProcess() {
    // Require a subaccount ID, otherwise the report might be huge
    // @todo And also if someone guesses the ID of another client, it can leak data.
    // (although we only provide this to a few partners)
    $subaccount_id = $this->_submitValues['subaccount_id_value'] ?? $this->_formValues['subaccount_id_value'] ?? NULL;

    if (!$subaccount_id) {
      CRM_Core_Session::setStatus(ts('Please enter a subaccount ID.'), ts('Error'), 'error');
      return;
    }

    parent::postProcess();
  }

  public function alterDisplay(&$rows) {
    parent::alterDisplay($rows);

    foreach ($rows as &$row) {
      $data = json_decode($row['civicrm_sparkpost_router_data']);
      $row['civicrm_sparkpost_router_subject'] = $data->subject ?? '';
      $row['civicrm_sparkpost_router_email'] = $data->rcpt_to ?? '';
      $row['civicrm_sparkpost_router_reason'] = $data->raw_reason ?? '';
    }
  }

}
