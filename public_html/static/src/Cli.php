<?php
/*
 <?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>https://www.otticait.com/</loc><lastmod>2023-06-15</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/puoi-fidarti</loc><lastmod>2023-05-02</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/termini-e-condizioni</loc><lastmod>2023-04-18</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/spedizioni-e-pagamenti</loc><lastmod>2023-04-18</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/recensioni-otticait-feedaty</loc><lastmod>2023-01-16</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/reso-cambio</loc><lastmod>2022-06-01</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/resi</loc><lastmod>2021-06-03</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/negozio</loc><lastmod>2021-05-25</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/informativa-cookie</loc><lastmod>2021-02-17</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/informativa-privacy</loc><lastmod>2021-02-17</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/no-route</loc><lastmod>2019-04-05</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url><url><loc>https://www.otticait.com/enable-cookies</loc><lastmod>2019-04-04</lastmod><changefreq>daily</changefreq><priority>0.2</priority></url></urlset>
 */

namespace Disual\StaticProducts;


use Disual\StaticProducts\Cli\CliTools;
use Disual\StaticProducts\Cli\ProductsRemover;
use Disual\StaticProducts\Cli\SitemapGenerator;
use Disual\StaticProducts\Core\AppArgs;
use Disual\StaticProducts\Magento1Connector\Magento1Connector;
use InvalidArgumentException;

class Cli {

  public function __construct() {
    AppArgs::parseArgs();
  }

  public function dispatch() {
    $time = self::getMicrotime();

    if (AppArgs::getRunTest()) {
      echo "Test\n";
      (new CliTools())->updateFeedaty();

    } elseif (AppArgs::getBuildSitemap()) {
      (new SitemapGenerator())->run();
    } elseif (AppArgs::getDeleteProducts()) {
      (new ProductsRemover())->run();
    } elseif (AppArgs::getFeedaty()) {
      (new CliTools())->updateFeedaty();
    } else {
      throw new InvalidArgumentException("parametri mancanti\n");
    }

    $elapsed = self::getMicrotime() - $time;
    $message = "\n-------------------------------\n"
      ."\$elapsed: $elapsed\n"
      ."-------------------------------\n";

    echo($message);
  }

  public static function getMicrotime() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
  }



}
