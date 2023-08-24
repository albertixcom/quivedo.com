<?php
/*
 <?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>https://www.otticait.com/</loc><lastmod>2023-06-15</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/puoi-fidarti</loc><lastmod>2023-05-02</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/termini-e-condizioni</loc><lastmod>2023-04-18</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/spedizioni-e-pagamenti</loc><lastmod>2023-04-18</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/recensioni-otticait-feedaty</loc><lastmod>2023-01-16</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/reso-cambio</loc><lastmod>2022-06-01</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/resi</loc><lastmod>2021-06-03</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/negozio</loc><lastmod>2021-05-25</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/informativa-cookie</loc><lastmod>2021-02-17</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/informativa-privacy</loc><lastmod>2021-02-17</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/no-route</loc><lastmod>2019-04-05</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/enable-cookies</loc><lastmod>2019-04-04</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url></urlset>
 */

namespace Disual\StaticProducts;


use Disual\StaticProducts\Core\AppArgs;
use Disual\StaticProducts\Core\AppContext;
use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppLogger;
use Disual\StaticProducts\Helpers\CacheHelper;
use Disual\StaticProducts\Orwell\OrwellHelper;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use InvalidArgumentException;
use Monolog\Logger;

class Cli {

  protected Query $db;

  protected Logger $logger;

  public function __construct() {
    $this->db = AppContext::getInstance()->getDB();
    $this->logger = AppLogger::getInstance()->getLogger();
    AppArgs::parseArgs();
  }

  public function dispatch() {
    $time = self::getMicrotime();

    if (AppArgs::getRunTest()) {
      echo "not implemented yet...\n";
    } elseif (AppArgs::getBuildSitemap()) {
      $this->generateSitemap();
    } else {
      throw new InvalidArgumentException("parametri mancanti\n");
    }

    $elapsed = self::getMicrotime() - $time;
    $message = "\n-------------------------------\n"
      ."\$elapsed: $elapsed\n"
      ."-------------------------------\n";

    echo($message);
  }

  /**
   * # At 05:00 on Monday.
   * 0 05 * * 1 /usr/local/bin/php /home/otticaitadm/public_html/static/main.php --build-sitemap
   */
  private function generateSitemap() {

    $query = "SELECT "."manufacturer_id FROM urls GROUP BY manufacturer_id";
    $data = $this->db->getPdo()->query($query)->fetchAll();
    foreach ($data As $item) {
      $manufacturerId = $item['manufacturer_id'];
      $manufacturerSlug = "manufacturer-".$manufacturerId;


      $query = "SELECT "."* FROM urls WHERE manufacturer_id = '$manufacturerId'";
      $chunkData = $this->db->getPdo()->query($query)->fetchAll();

      foreach (AppConfig::LANGS_X As $langCode => $_lang) {

        /** @noinspection PhpUnusedLocalVariableInspection */
        $storeId = $_lang['store_id'];

        $fileName = "sitemap-static-" . $langCode . "-".$manufacturerSlug.".xml";
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlContent .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $changefreq = 'daily';
        $priority   = '0.2';

        foreach ($chunkData As $_item) {
          $slug = $_item['url'];
          $lastmod = date('Y-m-d', $_item['created_at']);
          if ($langCode == AppConfig::DEFAULT_LANG) {
            $baseUrl = AppConfig::BASE_URL . $slug;
          } else {
            $baseUrl = AppConfig::BASE_URL . $langCode ."/" .$slug;
          }

          $xmlContent .= sprintf(
            '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
            htmlspecialchars($baseUrl),
            $lastmod,
            $changefreq,
            $priority
          );
        }

        $xmlContent .= '</urlset>';
        $destFile = APP_ROOT . '/../sitemap/'.$fileName;
        echo "Writing to file : [".$destFile."]\n";
        file_put_contents($destFile, $xmlContent);
      }
    }
    echo "DONE\n";
  }

  /** @noinspection PhpUnusedPrivateMethodInspection */
  private function updManufacturer() {

    $cacheDataPath = CacheHelper::getCachePath("/products/data");

    $query = "SELECT "."* FROM urls";
    $data = $this->db->getPdo()->query($query)->fetchAll();
    foreach ($data As $item) {
      $sku = $item['sku'];
      $hash = md5($sku);
      $productCacheFile =  $cacheDataPath."/".$hash.".json";
      $prData = json_decode(file_get_contents($productCacheFile), true);

      $manufacturerId = $prData['attributes']['manufacturer']['id'];

      $id = $item['id'];
      $this->db->getPdo()->query("UPDATE "."urls SET manufacturer_id = '$manufacturerId' WHERE id = '$id'")->execute();
    }
    echo "DONE\n";

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
   * @noinspection PhpUnusedPrivateMethodInspection
   */
  private function buildCacheDataFromUrls() {

    $cacheDataPath = CacheHelper::getCachePath("/products/data");
    $cacheImagesPath = CacheHelper::getCachePath("/products/images");

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

        $productCacheFile =  $cacheDataPath."/".$hash.".json";
        $productImageFile =  $cacheImagesPath."/".$hash.".jpg";

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




  public static function getMicrotime() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
  }

}
