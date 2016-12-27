<?php

class AdvancedBankTransferRegisterPaymentModuleFrontController extends ModuleFrontController {

  public $display_column_left = false;
  public $ssl = true;
  public $module;

  public function __construct()
  {
      // Se instancia el módulo en este controlador para poder obtener ciertas variables y ejecutar ciertas funciones
      // que están en él.
      $this->module = new AdvancedBankTransfer;

      parent::__construct();

      $this->context = Context::getContext();
  }

  public function initContent(){
    parent::initContent();

    $this->setTemplate('module:advancedbanktransfer/views/templates/front/registerPayment.tpl');
  }

  // Procesa el formulario en registerPayment.tpl
  public function postProcess()
  {
      if (Tools::isSubmit('submitPayment')){ // submitPayment se describe en el botón de enviar
        echo "hola";
      }
  }
}
