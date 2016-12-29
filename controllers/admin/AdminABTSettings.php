<?php
include_once(_PS_MODULE_DIR_.'advancedbanktransfer/advancedbanktransfer.php');

class AdminABTSettingsController extends ModuleAdminController {

  public $module;
  public $ssl = true;
  public $name = 'AdminABTSettings';

  public function __construct(){
    $this->bootstrap  = true;
    $this->module = new AdvancedBankTransfer;

    parent::__construct();

    $this->context = Context::getContext();
  }

  private function initBankList(){
    $fields_list = array(
        'id_bank' => array(
          'title' => $this->l('ID'),
            'width' => 140,
            'type' => 'text',
        ),
        'name' => array(
            'title' => $this->l('Name'),
            'width' => 140,
            'type' => 'text',
        ),
    );

    $helper = new HelperList();

    $helper->bulk_actions = array(
        'delete' => array(
            'text'    => $this->l('Delete'),
            'icon'    => 'icon-trash',
            'confirm' => $this->l('Delete selected items?'),
        ),
    );

    $helper->shopLinkType = '';

    $this->addRowAction('details');

    $helper->simple_header = true; // INTENTAR LUEGO HACER UN SORTING/SEARCH
    $helper->actions = array('edit', 'delete');
    $helper->identifier = 'id_bank';
    $helper->show_toolbar = true;
    $helper->title = '<i class="icon-university"></i> '.$this->l('Available Banks for Transfers/Deposits').' <span class="badge">qty</span>';
    $helper->table = $this->module->name.'_bank';
    $helper->token = Tools::getAdminTokenLite('AdminABTSettings');
    $helper->currentIndex = AdminController::$currentIndex;

    $model = new bankModel;
    $result = $model->getBanks('id_bank', 'asc');

    return $helper->generateList($result, $fields_list);
  }
  public function initBankAddForm() {
      // Get default language
      $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

      // Init Fields form array
      $fields_form[0]['form'] = array(
          'legend' => array(
              'title' => $this->l('Create new'),
          ),
          'input' => array(
              array(
                  'type' => 'text',
                  'label' => $this->l('Bank name'),
                  'name' => 'BANK_NAME',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'text',
                  'label' => $this->l('Account Holder'),
                  'name' => 'BANK_ACCOUNT_HOLDER',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'text',
                  'label' => $this->l('Account Number'),
                  'name' => 'BANK_ACCOUNT_NUMBER',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'textarea',
                  'label' => $this->l('Additional Info'),
                  'name' => 'ADDITIONAL_INFO',
                  'size' => 300,
                  'required' => false
              ),
          ),
          'submit' => array(
              'title' => $this->l('Save'),
              'class' => 'btn btn-default pull-right'
          ),
          'buttons' => array(
            array(
              'href' => AdminController::$currentIndex.'&configure='.'&token='.Tools::getAdminTokenLite('AdminModules'),
              'title' => $this->l('Back to list'),
              'icon' => 'process-icon-back',
            ),
          ),
      );

      $helper = new HelperForm();

      // Module, token and currentIndex
      $helper->module = $this;
      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

      // Language
      $helper->default_form_language = $default_lang;
      $helper->allow_employee_form_lang = $default_lang;

      $helper->fields_value['BANK_NAME'] = Configuration::get('BANK_NAME');
      $helper->fields_value['BANK_ACCOUNT_NUMBER'] = Configuration::get('BANK_ACCOUNT_NUMBER');
      $helper->fields_value['BANK_ACCOUNT_HOLDER'] = Configuration::get('BANK_ACCOUNT_HOLDER');
      $helper->fields_value['ADDITIONAL_INFO'] = Configuration::get('ADDITIONAL_INFO');

      return $helper->generateForm($fields_form);
  }

  public function initContent(){
    parent::initContent();

    $this->module->abtControllerTemplate($this->context, $this->initBankList().$this->initBankAddForm().$this->content, 'admin/view.tpl');
  }
}
