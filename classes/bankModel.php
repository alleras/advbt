<?php


class bankModel extends ObjectModel {

  public $id_bank;
  public $name;
  public $number;
  public $holder;
  public $enabled;
  public $info;

  public static $definition = array(
      'table' => 'advancedbanktransfer_banks',
      'primary' => 'id_bank',
      'multilang' => false, // Si es true, debo crear la tabla 'advancedbanktransfer_banks_lang'
      'fields' => array(
        // Normal fields
        'id_bank' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
        'number' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
        'holder' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255),
        'enabled' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        // Language fields
        'info' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 3999999),
      ),
  );

  public function __construct($id = null, $id_lang = null) {
      return parent::__construct($id, $id_lang);
  }

  public function installDB()
  {
      $return = true;
      $return &= Db::getInstance()->execute('
              CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'advancedbanktransfer_banks` (
              `id_bank` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `number` VARCHAR(255) NOT NULL,
              `holder` VARCHAR(255) NOT NULL,
              `enabled` INT NOT NULL,
              `info` TEXT NOT NULL,
              PRIMARY KEY (`id_bank`)
          ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'
      );
      return $return;
  }

  public function uninstallDB($drop_table = true)
  {
    $ret = true;
    if ($drop_table) {
        $ret &=  Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'advancedbanktransfer_banks`');
    }
    return $ret;
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

  public function numBankRows(){
    $sql = new DbQuery();
    $sql->select('COUNT(*)');
    $sql->from(self::$definition['table']);

    $result = Db::getInstance()->executeS($sql);

    return $result[0]['COUNT(*)'];
  }

  public function addBank(){



    return true;
  }

  public function deleteBank($id){
    $db = Db::getInstance();

    return Db::getInstance()->delete(self::$definition['table'], 'id_bank = '. $id);
  }

  public function updateBankInfo(){
    return true;
  }

  public function getBankInfo($id){
    $sql = new DbQuery();
    $sql->select('*');
    $sql->from('advancedbanktransfer_banks');
    $sql->where('id_bank = '.$id);

    $result = Db::getInstance()->getRow($sql);

    return $result;
  }
}
