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

    $model = new bankModel;
    $helper = new HelperList();

    $result = $model->getBanks('id_bank', 'asc');

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
    $helper->title =
      '<i class="icon-university"></i> '
      .$this->l('Available Banks for Transfers/Deposits')
      .' <span class="badge">'.(int)$model->numBankRows().'</span>';

    $helper->table = 'ABTBank';
    $helper->token = Tools::getAdminTokenLite('AdminABTSettings');
    $helper->currentIndex = AdminController::$currentIndex;

    return $helper->generateList($result, $fields_list);
  }

  public function initBankAddForm(array $bankData = null) {
      // Get default language
      $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

      // Init Fields form array
      $fields_form[0]['form'] = array(
          'legend' => array(
              'title' => '<i class="icon-plus-circle"></i> '.$this->l('Add bank'),
          ),
          'input' => array(
              array(
                  'type' => 'hidden',
                  'name' => 'BANK_ID',
                  'required' => false
              ),
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
      );

      $helper = new HelperForm();

      // Module, token and currentIndex
      $helper->module = $this;

      if(isset($bankData)){
        $fields_form[0]['form']['legend']['title'] = $this->l('Update Bank Info');
        $helper->submit_action = 'bank_upd';
        $fields_form[0]['form']['buttons'] = array(
          array(
          'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminABTSettings'),
          'title' => $this->l('Back'),
          'icon' => 'process-icon-back',
          ),
        );
      }
      else{
        $helper->submit_action = 'bank_add';
      }

      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminABTSettings');
      $helper->currentIndex = AdminController::$currentIndex;

      // Language
      $helper->default_form_language = $default_lang;
      $helper->allow_employee_form_lang = $default_lang;

      $helper->fields_value['BANK_ID'] = $bankData['id_bank'];
      $helper->fields_value['BANK_NAME'] = $bankData['name'];
      $helper->fields_value['BANK_ACCOUNT_NUMBER'] = $bankData['number'];
      $helper->fields_value['BANK_ACCOUNT_HOLDER'] = $bankData['holder'];
      $helper->fields_value['ADDITIONAL_INFO'] = $bankData['info'];

      return $helper->generateForm($fields_form);
  }

  public function initContent(){
    parent::initContent();
    $output = '';
    $form = $this->initBankAddForm();

    if(Tools::isSubmit('deleteABTBank') && Validate::isInt(Tools::getValue('id_bank'))){
      $model = new bankModel;
      $model->deleteBank(Tools::getValue('id_bank'));
    }
    if(Tools::isSubmit('submitBulkdeleteABTBank')){
      $banksIDArray = Tools::getValue('ABTBankBox');
      foreach($banksIDArray as $bankID){
        $model = new bankModel;
        $model->deleteBank($bankID);
      }
    }

    // Chequeo si se está añadiendo o actualizando un banco
    if(Tools::isSubmit('bank_add') || Tools::isSubmit('bank_upd')){
      $formBankData = array(
        'bankName' => strval(Tools::getValue('BANK_NAME')),
        'bankAccountNumber' => strval(Tools::getValue('BANK_ACCOUNT_NUMBER')),
        'bankAccountHolder' => strval(Tools::getValue('BANK_ACCOUNT_HOLDER')),
        'additional' => strval(Tools::getValue('ADDITIONAL_INFO')),
      );
      // Antes que nada, se validan los datos
      if (!isset($formBankData['bankName'])
        || !isset($formBankData['bankAccountNumber'])
        || !isset($formBankData['bankAccountHolder'])
        || !isset($formBankData['additional'])
        || empty($formBankData['bankName'])
        || empty($formBankData['bankAccountNumber'])
        || empty($formBankData['bankAccountHolder'])
        || !Validate::isGenericName($formBankData['bankName'])
        || !Validate::isGenericName($formBankData['bankAccountNumber'])
        || !Validate::isGenericName($formBankData['bankAccountHolder'])
      )
          $output .= $this->module->displayError($this->l('Invalid or missing data in bank configuration'));
      else
      {
        $model = new bankModel;
        // Se Chequea si se está añadiendo
        if(Tools::isSubmit('bank_add')){
          $model->name = $formBankData['bankName'];
          $model->number = $formBankData['bankAccountNumber'];
          $model->holder = $formBankData['bankAccountHolder'];
          $model->enabled = 1;
          $model->info = $formBankData['additional'];

          $model->add();
          $output .= $this->module->displayConfirmation($this->l('Bank added'));
        }
        // Se Chequea si se está actualizando
        elseif(Tools::isSubmit('bank_upd')){
          $model = new bankModel;

          $formBankData['id'] = (int)Tools::getValue('BANK_ID');

          $model->id = $formBankData['id']; // Dato necesario para la cláusula WHERE de update()
          $model->id_bank = $formBankData['id'];
          $model->name = $formBankData['bankName'];
          $model->number = $formBankData['bankAccountNumber'];
          $model->holder = $formBankData['bankAccountHolder'];
          $model->enabled = 1; // CAMBIAR
          $model->info = $formBankData['additional'];

          $model->update();

          $output .= $this->module->displayConfirmation($this->l('Bank Updated'));
        }
      }
    }

    if(Tools::isSubmit('updateABTBank') && Validate::isInt(Tools::getValue('id_bank'))){
      $model = new bankModel;
      $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/scrollBottom.js');
      $form = $this->initBankAddForm($model->getBankInfo(Tools::getValue('id_bank')));
    }

    $qtyCheckModel = new bankModel;
    if($qtyCheckModel->numBankRows() == 0)
      $output .= $this->module->abtAddToTemplate($this->context,'hook/noBankInfo.tpl');

    $list = $this->initBankList();

    $this->module->abtControllerTemplate($this->context, $output.$list.$form, 'admin/view.tpl');
  }
}
