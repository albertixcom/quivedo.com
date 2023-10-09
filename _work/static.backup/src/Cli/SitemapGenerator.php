<?php


namespace Disual\StaticProducts\Cli;

use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppContext;
use Disual\StaticProducts\Core\AppLogger;
use Disual\StaticProducts\Magento1Connector\Magento1Connector;
use Envms\FluentPDO\Query;
use Monolog\Logger;

class SitemapGenerator {

  protected Query $db;
  protected Logger $logger;
  protected Magento1Connector $connector;

  public function __construct() {
    $this->db = AppContext::getInstance()->getDB();
    $this->logger = AppLogger::getInstance()->getLogger();
    $this->connector = new Magento1Connector();
  }

  /**
   * # At 05:00 on Monday.
   * 0 05 * * 1 /usr/local/bin/php /home/otticaitadm/public_html/static/main.php --build-sitemap
   */
  public function run() {
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
        $xmlContent .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

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

}
