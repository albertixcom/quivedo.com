<?php /** @noinspection DuplicatedCode */

namespace Disual\StaticProducts\Core;

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

  public static string $curLang = 'it';

  private function __construct() {


    $appConfig = AppConfig::getInstance();
    $dbName = $appConfig->getConfig()['sqlite']['dbname'];
    $dbPath = APP_ROOT . "/data/".$dbName;

    $pdo = new PDO("sqlite:$dbPath", null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

    //
    // Database
    //
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
