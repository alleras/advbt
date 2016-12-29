<?php
include_once(_PS_MODULE_DIR_.'advancedbanktransfer/advancedbanktransfer.php');

class AdminABTSettingsController extends ModuleAdminController {

  public $module;
  public $ssl = true;

  public function __construct(){
    $this->bootstrap  = true;
    $this->module = new AdvancedBankTransfer;

    parent::__construct();

    $this->context = Context::getContext();
  }

  private function initBankList(){
    $this->fields_list = array(
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
    // Actions to be displayed in the "Actions" column
    $helper->actions = array('edit', 'delete');

    $helper->identifier = 'id_bank';
    $helper->show_toolbar = true;
    $helper->title = '<i class="icon-university"></i> '.$this->l('Available Banks for Transfers/Deposits').' <span class="badge">qty</span>';
    $helper->table = $this->module->name.'_bank';

    $helper->token = Tools::getAdminTokenLite('AdminABTSettings');
    $helper->currentIndex = AdminController::$currentIndex;

    $model = new bankModel;
    $result = $model->getBanks('id_bank', 'asc');

    return $helper->generateList($result, $this->fields_list);
  }

  public function initContent(){
    parent::initContent();

    $this->module->abtControllerTemplate($this->context, $this->initBankList().$this->content);
  }
}
