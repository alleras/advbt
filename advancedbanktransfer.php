<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class AdvancedBankTransfer extends PaymentModule {

  public $tabs = array();

  public function __construct() {
    $this->name = 'advancedbanktransfer';
    $this->tab = 'payments_gateway';
    $this->version = '0.0.1';
    $this->author = 'Agustin Lleras';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;

    $this->controllers = array('registerPayment');

    $this->tabs[] = array(
        'name'      => $this->l('Advanced Bank Transfer Settings'),
        'className' => 'AdminABTSettings',
        'active'    => 1,
    );
    $this->tabs[] = array(
        'name'      => $this->l('Pending Transfers'),
        'className' => 'AdminABTPendingTransfers',
        'active'    => 1,
    );

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
      !$this->registerHook('displayCustomerAccount') ||
      !$this->addTab($this->tabs, 55))
      return false;
    return true;
  }

  public function uninstall() {
    if (!parent::uninstall() ||
        !$this->removeTab($this->tabs))
      return false;
    return true;
  }

  public function addTab($tabs, $id_parent = 0){
      foreach ($tabs as $tab)
      {
          $tabModel             = new Tab();
          $tabModel->module     = $this->name;
          $tabModel->active     = $tab['active'];
          $tabModel->class_name = $tab['className'];
          $tabModel->id_parent  = $id_parent;

          //tab text in each language
          foreach (Language::getLanguages(true) as $lang)
          {
              $tabModel->name[$lang['id_lang']] = $tab['name'];
          }

          $tabModel->add();

          //submenus of the tab
          if (isset($tab['childs']) && is_array($tab['childs']))
          {
              $this->addTab($tab['childs'], Tab::getIdFromClassName($tab['className']));
          }
      }
      return true;
  }

  private function removeTab($tabs){
      foreach ($tabs as $tab)
      {
          $id_tab = (int) Tab::getIdFromClassName($tab["className"]);
          if ($id_tab)
          {
              $tabModel = new Tab($id_tab);
              $tabModel->delete();
          }

          if (isset($tab["childs"]) && is_array($tab["childs"]))
          {
              $this->removeTab($tab["childs"]);
          }
      }

      return true;
  }

  /*public function displayBankForm() {
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
              'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
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

      // Load current value
      $helper->fields_value['BANK_NAME'] = Configuration::get('BANK_NAME');
      $helper->fields_value['BANK_ACCOUNT_NUMBER'] = Configuration::get('BANK_ACCOUNT_NUMBER');
      $helper->fields_value['BANK_ACCOUNT_HOLDER'] = Configuration::get('BANK_ACCOUNT_HOLDER');
      $helper->fields_value['ADDITIONAL_INFO'] = Configuration::get('ADDITIONAL_INFO');


      return $helper->generateForm($fields_form);
  }*/

  /*public function getContent() {
      $output = null;

      $this->context->smarty->assign(
        array(
          'current_url' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name,
        )
      );
      if(Tools::getValue('add_new_bank')){
        return $this->addNewBank();
      }

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
      return $this->display(__FILE__, 'views/templates/admin/view.tpl');
  }*/

  public function hookPaymentOptions() {
    // todo
  }

  public function hookPaymentReturn() {
    // todo
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
