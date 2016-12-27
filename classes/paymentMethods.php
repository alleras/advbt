<?php
class paymentMethods extends ObjectModel {
  public static $definition = array(
      'table' => 'advancedbanktransfer_banks',
      'primary' => 'id_bank',
      'multilang' => true,
      'fields' => array(
        // Normal fields
        'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
        'holder' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255),
        'enabled' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        // Language fields
        'info' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 3999999),
      ),
  );

  public function __construct($id = null, $id_lang = null) {
      return parent::__construct($id, $id_lang);
  }

  public function addBank(){
    return true;
  }

  public function updateBankInfo(){
    return true;
  }

  public function deleteBank(){
    return true;
  }

  public function getBankInfo(){
    return true;
  }
}
