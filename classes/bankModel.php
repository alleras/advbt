<?php


class bankModel extends ObjectModel {

  public $id_bank;
  public $name;
  public $holder;
  public $enabled;
  public $info;

  public static $definition = array(
      'table' => 'advancedbanktransfer_banks',
      'primary' => 'id_bank',
      'multilang' => true,
      'fields' => array(
        // Normal fields
        'id_bank' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
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

  public function getBanks(string $orderBy = 'id_bank', string $orderType = null){
    $sql = new DbQuery();
    $sql->select('*');
    $sql->from('advancedbanktransfer_banks');

    if($orderType)
      $orderBy = $orderBy.' '.$orderType;

    if($orderBy != 'id_bank')
      $sql->orderBy($orderBy);
    else
      $sql->orderBy('id_bank'.$orderType);

    return Db::getInstance()->executeS($sql);
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
