<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class AdvancedBankTransfer extends PaymentModule {

  public function __construct() {
    $this->name = 'advancedbanktransfer';
    $this->tab = 'payments_gateway';
    $this->version = '0.0.1';
    $this->author = 'Agustin Lleras';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;

    $this->controllers = array('registerPayment');

    parent::__construct();

    $this->displayName = $this->l('Advanced Bank Transfer Module');
    $this->description = $this->l('TODO');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
  }

  public function install() {
    if (Shop::isFeatureActive())
      Shop::setContext(Shop::CONTEXT_ALL);

    if (!parent::install() ||
      !$this->registerHook('hookPaymentOptions') ||
      !$this->registerHook('hookPaymentReturn') ||
      !$this->registerHook('displayCustomerAccount')
    )
      return false;
    return true;
  }
  public function uninstall() {
    if (!parent::uninstall())
      return false;
    return true;
  }
  public function displayForm() {
      // Get default language
      $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

      // Init Fields form array
      $fields_form[0]['form'] = array(
          'legend' => array(
              'title' => $this->l('Settings'),
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
          )
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

      // Title and toolbar
      $helper->title = $this->displayName;
      $helper->show_toolbar = true;        // false -> remove toolbar
      $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
      $helper->submit_action = 'submit'.$this->name;
      $helper->toolbar_btn = array(
          'save' =>
          array(
              'desc' => $this->l('Save'),
              'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
              '&token='.Tools::getAdminTokenLite('AdminModules'),
          ),
          'back' => array(
              'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
              'desc' => $this->l('Back to list')
          )
      );

      // Load current value
      $helper->fields_value['BANK_NAME'] = Configuration::get('BANK_NAME');
      $helper->fields_value['BANK_ACCOUNT_NUMBER'] = Configuration::get('BANK_ACCOUNT_NUMBER');
      $helper->fields_value['BANK_ACCOUNT_HOLDER'] = Configuration::get('BANK_ACCOUNT_HOLDER');
      $helper->fields_value['ADDITIONAL_INFO'] = Configuration::get('ADDITIONAL_INFO');


      return $helper->generateForm($fields_form);
  }
  public function getContent() {
      $output = null;

      if (Tools::isSubmit('submit'.$this->name))
      {
          $bankName = strval(Tools::getValue('BANK_NAME'));
          $bankAccountNumber = strval(Tools::getValue('BANK_ACCOUNT_NUMBER'));
          $bankAccountHolder = strval(Tools::getValue('BANK_ACCOUNT_HOLDER'));
          $additional = strval(Tools::getValue('ADDITIONAL_INFO'));

          if (!$bankName || !$bankAccountNumber || !$bankAccountHolder || !$additional
            || empty($bankName) || empty($bankAccountNumber) || empty($bankAccountHolder)
            || !Validate::isGenericName($bankAccountNumber)
            || !Validate::isGenericName($bankName)
            || !Validate::isGenericName($bankAccountHolder)
          )
              $output .= $this->displayError($this->l('Invalid Configuration value'));
          else
          {
              Configuration::updateValue('BANK_NAME', $bankName);
              Configuration::updateValue('BANK_ACCOUNT_NUMBER', $bankAccountNumber);
              Configuration::updateValue('BANK_ACCOUNT_HOLDER', $bankAccountHolder);
              Configuration::updateValue('ADDITIONAL_INFO', $additional);


              $output .= $this->displayConfirmation($this->l('Settings updated'));
          }
      }
      return $output.$this->displayForm();
  }

  public function hookPaymentOptions() {

  }

  public function hookPaymentReturn() {

  }

  public function hookDisplayCustomerAccount($params) {

    $this->context->smarty->assign(
        array(
            'paymentLink' => $this->context->link->getModuleLink('advancedbanktransfer', 'registerPayment')
        )
    );
    return $this->display(__FILE__, 'displayCustomerAccount.tpl');
  }
}
