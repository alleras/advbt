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
      !$this->registerHook('paymentOptions') ||
      !$this->registerHook('paymentReturn') ||
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
  public function hookPaymentOptions($params)
  {
      if (!$this->active) {
          return;
      }

      $newOption = new PaymentOption();

      $newOption->setCallToActionText($this->trans('Pay by transfer or deposit', array(), 'Modules.AdvancedBankTransfer.Shop'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setAdditionalInformation('hey'/*$this->fetch('module:ps_wirepayment/views/templates/hook/ps_wirepayment_intro.tpl')*/);
      $payment_options = [
          $newOption,
      ];

      return $payment_options;
  }


  public function hookPaymentReturn($params) {
    if (!$this->active) {
        return;
    }
    $state = $params['order']->getCurrentState();

    $model = new bankModel;

    $banks = $model->getBanks();

    $this->smarty->assign(array(
        'banks' => $banks,
        'shop_name' => $this->context->shop->name,
        'total' => Tools::displayPrice(
            $params['order']->getOrdersTotalPaid(),
            new Currency($params['order']->id_currency),
            false
          ),
        'status' => 'ok',
        'reference' => $params['order']->reference,
        'contact_url' => $this->context->link->getPageLink('contact', true)
    ));

    return $this->fetch('module:advancedbanktransfer/views/templates/hook/payment_return.tpl');
  }

  public function hookDisplayOrderDetail(){

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
