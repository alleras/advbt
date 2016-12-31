<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_.'advancedbanktransfer/classes/bankModel.php';
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class AdvancedBankTransfer extends PaymentModule {

  public $models = array('bankModel');
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
    $model = new bankModel;

    if (Shop::isFeatureActive())
      Shop::setContext(Shop::CONTEXT_ALL);

    if (!parent::install() ||
      !$this->registerHook('hookPaymentOptions') ||
      !$this->registerHook('hookPaymentReturn') ||
      !$this->registerHook('displayCustomerAccount') ||
      !$this->addTab($this->tabs, 55) ||
      !$model->installDB())
      return false;
    return true;
  }

  public function uninstall() {
    $model = new bankModel;
    if (!parent::uninstall() ||
        !$this->removeTab($this->tabs) ||
        !$model->uninstallDB())
      return false;
    return true;
  }

  // Misc
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

  public function abtControllerTemplate($context, $content, string $fileLocation = null){
    $smarty = $context->smarty;
    $addContent = '';

    if(isset($fileLocation))
      $addContent = $smarty->fetch(_PS_MODULE_DIR_ . 'advancedbanktransfer/views/templates/'.$fileLocation);

    $this->context->smarty->assign(
      array(
        'content' => $content . $addContent
      )
    );
    return true;
  }
  public function abtAddToTemplate($context, string $fileLocation = null){
    $smarty = $context->smarty;

    if(isset($fileLocation))
      $addContent = $smarty->fetch(_PS_MODULE_DIR_ . 'advancedbanktransfer/views/templates/'.$fileLocation);
    return $addContent;
  }

  // Hooks
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
