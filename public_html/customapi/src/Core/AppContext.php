<?php

namespace Disual\CustomApi\Core;

use Envms\FluentPDO\Query;
use PDO;


class AppContext {

  use Singleton;

  /**
   * query builder
   *
   * @var Query
   */
  protected Query $db;

  private function __construct() {


    $appConfig = AppConfig::getInstance();
    $configDb = $appConfig->getConfig()['mysql'];

    //
    // Database
    //
    $pdo = new PDO("mysql:dbname=".$configDb['dbname'].';charset=utf8', $configDb['user'], $configDb['pass'],
      array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//        PDO::ATTR_PERSISTENT => false,
//        PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8mb4 collate utf8mb4_unicode_ci"
      ));
    $this->db = new Query($pdo);
//    $this->db->debug = true;
  }

  /**
   *
   * @return Query
   */
  public function getDB(): Query {
    return $this->db;
  }

}
