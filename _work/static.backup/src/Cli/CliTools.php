<?php /** @noinspection PhpUnused */


namespace Disual\StaticProducts\Cli;


use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppContext;
use Disual\StaticProducts\Core\AppLogger;
use Disual\StaticProducts\Magento1Connector\Magento1Connector;
use Disual\StaticProducts\Orwell\OrwellHelper;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use Monolog\Logger;

class CliTools {

  protected Query $db;
  protected Logger $logger;
  protected Magento1Connector $connector;

  public function __construct() {
    $this->db = AppContext::getInstance()->getDB();
    $this->logger = AppLogger::getInstance()->getLogger();
    $this->connector = new Magento1Connector();
  }

  /**
   * Questo tool permette di generare la cache delle informazioni
   * dei prodotti e immagine principale.
   * Il metodo usa stesse funzionalita di web durante analisi del url
   * Nel caso del WEB l'informazione sul url risiede già nel database
   * mentre in questo metodo, url viene ricavato da una lista dei url.
   * Questa lista era presente nel htaccess per fare un redirect verso produttore
   *
   * I dati di test:
   * $mockSku = 'CK5469 035';
   * $mockUrl = 'calvin-klein-ck5469-035';
   *
   * Esempio del risultato:
   * https://www.otticait.com/calvin-klein-ck5469-035
   * https://www.otticait.com/en/calvin-klein-ck5469-035
   *
   * Come lanciare CLI (da riga di commando del otticait):
   * > php /home/otticaitadm/public_html/static/main.php
   *
   * @noinspection PhpUnused
   */
  public function buildCacheDataFromUrls() {

    $listFile = APP_ROOT . "/data/rewrite_list.txt";
    $lines = file($listFile);
    $i = 1;
    $tot = count($lines);
    foreach($lines as $line) {
      $line = trim($line);

      //
      // url potrebbero contenere prefisso della lingua che deve essere eliminato
      //
      $_langs = array_keys(AppConfig::LANGS_X);
      $langs = implode('|',$_langs);
      $slug = preg_replace('#^/('.$langs.')/#', '', $line);
      $url = ltrim($slug, '/');

      echo "($i/$tot) [$url]\n";

      //
      // Elaborazione solo dei url non ancora registrati nel database
      //
      $query = "SELECT "."* FROM urls WHERE url = '$url'";
      $dbData = $this->db->getPdo()->query($query)->fetch();
      if ($dbData) {
        $i++;
        continue;
      }

      //
      // Informazioni del prodotto vengono presi attraverso le API da ORWELL
      //
      $productData = OrwellHelper::fetchRemoteData(null, $slug);

      if ($productData) {

        $sku = $productData['sku'];

        // -- NOTA: univocita del nome del file di cache per oviare problemi con nomi latini o non POSIX
        $hash = md5($sku);

        $productCacheFile =  AppContext::getCacheDataPath()."/".$hash.".json";
        $productImageFile =  AppContext::getCacheImagesPath()."/".$hash.".jpg";

        //
        // Caching delle info e del immagine (files separati)
        //
        if (!empty($productData['image'])) {
          $blob = base64_decode($productData['image']);
          file_put_contents($productImageFile, $blob);
        }
        unset($productData['image']);

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
          echo "ERROR > ".$e->getMessage()."\n";
          die();
        }
      } else {
        echo "ERRR\n";
        die();
      }
      $i++;
    }
    echo "\nDONE\n";
  }

  public function updManufacturer() {

    $query = "SELECT "."* FROM urls";
    $data = $this->db->getPdo()->query($query)->fetchAll();
    foreach ($data As $item) {
      $sku = $item['sku'];
      $hash = md5($sku);
      $productCacheFile =  AppContext::getCacheDataPath()."/".$hash.".json";
      $prData = json_decode(file_get_contents($productCacheFile), true);

      $manufacturerId = $prData['attributes']['manufacturer']['id'];

      $id = $item['id'];
      $this->db->getPdo()->query("UPDATE "."urls SET manufacturer_id = '$manufacturerId' WHERE id = '$id'")->execute();
    }
    echo "DONE\n";

  }

}
