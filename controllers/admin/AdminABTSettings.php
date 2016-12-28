<?php
include_once(_PS_MODULE_DIR_.'advancedbanktransfer/advancedbanktransfer.php');

class AdminABTSettingsController extends ModuleAdminController {

  public $module;
  public $ssl = true;
  public $moduleLocation;

  public function __construct(){
    $this->bootstrap  = true;
    $this->module = new AdvancedBankTransfer;
    $this->moduleLocation = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'advancedbanktransfer.php';
    parent::__construct();

    $this->context = Context::getContext();
  }

  public function initContent(){
    parent::initContent();
    
    $smarty = $this->context->smarty;
    $content = $smarty->fetch(_PS_MODULE_DIR_ . 'advancedbanktransfer/views/templates/admin/view.tpl');
    $this->context->smarty->assign(array('content' => $this->content . $content));
  }
}
