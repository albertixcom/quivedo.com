<?php


namespace Disual\StaticProducts\Cli;


use Disual\StaticProducts\Core\AppArgs;
use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppContext;
use Disual\StaticProducts\Core\AppLogger;
use Disual\StaticProducts\Magento1Connector\Magento1Connector;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use Monolog\Logger;

class ProductsRemover {

  protected Query $db;
  protected Logger $logger;
  protected Magento1Connector $connector;

  private bool $productExists;

  public function __construct() {
    $this->db = AppContext::getInstance()->getDB();
    $this->logger = AppLogger::getInstance()->getLogger();
    $this->connector = new Magento1Connector();
  }

  /**
   * EX: FOS 104390 FWM
   */
  public function run() {
    // lista dei prodotti da eliminare
    $listFile = APP_ROOT . "/data/products_to_delete.txt";
    if (!file_exists($listFile)) {
      echo "Nothing to do...\n";
      return;
    }

    $hasErrors = false;
    $lines = file($listFile);
    $i = 1;
    $tot = count($lines);
    foreach($lines as $line) {
      $sku = trim($line);

      if (empty($sku)) {
        $i++;
        continue;
      }

      echo "($i/$tot) [$sku]\n";

      $error = '';
      $result = $this->buildProductCache($sku, $error);
      if (!$result) {
        echo "ERROR > " . $error . "\n";
        $hasErrors = true;
        $i++;
        continue;
      }

      if (!AppArgs::getNoDelete() && $this->productExists) {
        $error = '';
        echo "Deleting product from Magento...\n";
        $result = $this->connector->deleteProductExtended($sku, $error);
        if (!$result) {
          echo "ERROR > " . $error . "\n";
          $hasErrors = true;
          $i++;
          continue;
        }
      }

      $i++;
    }

    if (!$hasErrors) {
      if (!AppArgs::getNoDelete()) {
        echo "Removing file: $listFile\n";
        unlink($listFile);
      }
    }
    echo "\nDONE\n";
  }

  /**
   * @param string $sku
   * @param string $error eventuale errore
   * @param bool $force se deve rigenerare la cache
   * @return bool
   * @noinspection PhpSameParameterValueInspection
   */
  private function buildProductCache(string $sku, string &$error = '', bool $force = false): bool {

    $this->productExists = true;

    //
    // Elaborazione solo dei url non ancora registrati nel database
    //
    $query = "SELECT "."* FROM urls WHERE sku = '$sku'";
    $dbData = $this->db->getPdo()->query($query)->fetch();
    if ($dbData) {
      if ($force) {
        $this->db->getPdo()->exec("DELETE "." FROM urls WHERE sku = '$sku'");
      } else {
        $this->productExists = false;
        return true;
      }
    }

    //
    // Informazioni del prodotto vengono presi attraverso le API da ORWELL
    //
    $productData = $this->connector->getProductExtended($sku);

    if (!$productData) {
      echo "[$sku] Product not found!\n";
      //$error = "Product not found: [$sku]";
      $this->productExists = false;
      return true;
    }

    echo "[$sku] Building product cache...\n";

    $slug = $productData['attributes']['url_key']['value'];
    unset($productData['attributes']['url_key']);

    // -- NOTA: univocita del nome del file di cache per oviare problemi con nomi latini o non POSIX
    $hash = md5($sku);

    $productCacheFile =  AppContext::getCacheDataPath()."/".$hash.".json";
    $productImageFile =  AppContext::getCacheImagesPath()."/".$hash.".jpg";

    //
    // Caching delle info e del immagine (files separati)
    //
    if (!empty($productData['image'])) {
      if (file_exists($productData['image'])) {
        copy($productData['image'], $productImageFile);
      }
    }
    unset($productData['image']);
    $productData['description'] = $this->getDescription($sku);

    $json = json_encode($productData, JSON_PRETTY_PRINT);
    file_put_contents($productCacheFile, $json);

    //
    // Salvataggio nel DB di supporto
    //
    $set = [
      'url' => $slug,
      'created_at' => time(),
      'sku' => $sku,
      'manufacturer_id' => $productData['attributes']['manufacturer']['id']
    ];

    try {

      $this->db->insertInto('urls', $set)->execute();

    } catch (Exception $e) {
      $error = "ERROR > ".$e->getMessage();
      return false;
    }
    return true;
  }

  private function getDescription(string $sku): array {
    $descriptions = [];
    foreach (AppConfig::LANGS_X As $code => $lang) {
      $storeId = $lang['store_id'];
      $descriptions[$code] =  $this->connector->getProductAttributeValue($sku, 'description', $storeId);
    }
    return $descriptions;
  }

}
