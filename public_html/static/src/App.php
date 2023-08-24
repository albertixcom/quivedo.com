<?php
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */

/**
 * DA IMPOSTARE PER DOMINIO
 * - costanti nel file AppConfig
 * - su ORWELL public_html/admintools/src/Orwell/OrwellConnector.php aggiungere campi dei attributi
 *
 *
 *
 */
namespace Disual\StaticProducts;

use Disual\StaticProducts\Core\AppConfig;
use Disual\StaticProducts\Core\AppContext;
use Disual\StaticProducts\Helpers\CacheHelper;
use Disual\StaticProducts\Helpers\MagentoHelper;
use Disual\StaticProducts\Http\RequestFactory;
use Disual\StaticProducts\Http\RequestUtils;
use Disual\StaticProducts\I18N\Lang;
use Disual\StaticProducts\Orwell\OrwellHelper;
use Envms\FluentPDO\Query;

class App {

  protected Query $db;

  private static array $templateVars = [];

  public static function get(string $name) {
    if (isset(self::$templateVars[$name])) {
      return self::$templateVars[$name];
    }
    return null;
  }

  private static function setTemplateVars($values) {
    self::$templateVars = $values;
  }

  public function __construct() {
    $this->db = AppContext::getInstance()->getDB();
  }

  /**
   * Controllo di esistenza del url del prodotto eliminato.
   *
   * Url dei prodotti eliminati vengono salvati nel db di supporto (sqlite)
   * e durante la richiesta web, viene controllata eventuale esistenza del url richiesto.
   * Se url viene trovato, vengono prelevate le informazioni dalla
   * cache del prodotto e immagine (due files separati).
   * A base di queste informazioni viene cotruito output html.
   * Dato che questo file viene chiamato internamente da MAGENTO,
   * serve semplicemente come proxy ed in caso di url non registrato,
   * prosegue con l'elaborazioni interne di MAGENTO (visualizza pagina 404)
   *
   * Esempi:
   * https://www.otticait.com/calvin-klein-ck5469-035
   * https://www.otticait.com/en/calvin-klein-ck5469-035
   *
   */
  public function dispatch() {

    $request = RequestFactory::fromGlobals();
    $requestUrl = $request->getUri()->getPath();

    //
    // Gestione della lingua. In caso url contiene nel primo segmento
    // codice della lingua, questa viene salvata nel contesto
    //
    $lang = AppConfig::DEFAULT_LANG;
    $_lang = RequestUtils::getPathSegment($request, 1);
    $langs = array_keys(AppConfig::LANGS_X);
    if (!empty($_lang) && in_array($_lang,$langs)) {
      $lang = $_lang;
    }
    AppContext::$curLang = $lang;

    //
    // Url deve esse "puro" senza codice lingua
    //
    $langs = array_keys(AppConfig::LANGS_X);
    if ($lang != AppConfig::DEFAULT_LANG && in_array($lang,$langs)) {
      $requestUrl = preg_replace('#^/'.$lang.'/#', '',$requestUrl );
    }

    //
    // SLUG url si riferisce a url richiesto senza lingua
    //
    $slug = ltrim($requestUrl, '/');

    //
    // Check nel database
    //
    $query = "SELECT "."* FROM urls WHERE url = '$slug'";
    $dbData = $this->db->getPdo()->query($query)->fetch();
    if (!$dbData) {
      return;
    }

    //
    // --------- MAIN ------------
    //
    $sku = $dbData['sku'];

    $data = $this->fetchCachedData($sku);
    if (!$data) {
      $data = OrwellHelper::fetchRemoteData($sku);
      if ($data) {
        $this->storeCacheData($data);
      }
    }

    if (!$data) {
      return;
    }

    $this->formatData($data);
    // url potrebbe contenere la lingua (questo viene costruito da Magento)
    $baseUrl = \Mage::getBaseUrl();
    // root url (senza lingua)
    $rootUrl = AppConfig::BASE_URL;

    $templateVars = [
      'productData' => $data,
      'baseUrl' => $baseUrl,
      'rootUrl' => $rootUrl,
      'slug' => $slug
    ];

    self::setTemplateVars($templateVars);

    include APP_ROOT . "/data/template/product.phtml";

    // -- termina qui (non deve passare oltre)
    die();

  }

  /**
   * @param string $sku
   * @return array|null
   */
  private function fetchCachedData(string $sku):?array {

    $cacheDataPath = CacheHelper::getCachePath("/products/data");
    $cacheImagesPath = CacheHelper::getCachePath("/products/images");

    $hash = md5($sku);
    $productCacheFile =  $cacheDataPath."/".$hash.".json";
    $productImageFile =  $cacheImagesPath."/".$hash.".jpg";

    if (file_exists($productCacheFile)) {
      $productData['image_url'] = '';
      $json = file_get_contents($productCacheFile);
      $productData = json_decode($json, true);

      if (file_exists($productImageFile)) {
        $productImageUrl = CacheHelper::urlFromCache("/products/images/".$hash.".jpg");
        $productData['image_url'] = $productImageUrl;
      }
      return $productData;
    }
    return null;
  }

  /**
   * @param array $productData
   */
  private function storeCacheData(array &$productData) {

    $sku = $productData['sku'];

    $cacheDataPath = CacheHelper::getCachePath("/products/data");
    $cacheImagesPath = CacheHelper::getCachePath("/products/images");

    $hash = md5($sku);
    $productCacheFile =  $cacheDataPath."/".$hash.".json";
    $productImageFile =  $cacheImagesPath."/".$hash.".jpg";

    if (!empty($productData['image'])) {
      // immagine viene salvata
      $blob = base64_decode($productData['image']);
      file_put_contents($productImageFile, $blob);
      $productImageUrl = CacheHelper::urlFromCache("/products/images/".$hash.".jpg");
      $productData['image_url'] = $productImageUrl;
    }
    unset($productData['image']);
    $json = json_encode($productData, JSON_PRETTY_PRINT);
    file_put_contents($productCacheFile, $json);
  }

  /**
   * @param array $productData
   */
  private function  formatData(array &$productData) {


    $baseUrl = \Mage::getBaseUrl();
    $curLang = AppContext::$curLang;

    // mapping dei nome di attributi
    $_attributes = [];
    $allAttributesNames = MagentoHelper::getAllAttributesNames($curLang);
    foreach ($productData['attributes'] As $attrCode => $attribute) {
      $label = '';
      if (isset($allAttributesNames[$attrCode])) {
        $label = $allAttributesNames[$attrCode]['label'];
        if ($curLang != AppConfig::DEFAULT_LANG && Lang::is($label)) {
          $label = Lang::l($label);
        }
      }

      if ($attribute['type'] == 'multiselect') {
        $_values = [];
        foreach ($attribute['values'] As $item) {
          $_v = $item['value'];
          if (Lang::is($_v)) {
            $_v = Lang::l($_v);
          }
          $_values[] = $_v;
        }

        if (!empty($_values)) {
          $values = implode(',', $_values);
          $_attributes[$attrCode] = [
            'label' => $label,
            'value' => $values,
            'type' => 'multiselect'
          ];
        }
      } else {
        if (!empty($attribute['value'])) {
          $_v = $attribute['value'];
          if (Lang::is($_v)) {
            $_v = Lang::l($_v);
          }
          $_attributes[$attrCode] = [
            'label' => $label,
            'value' => $_v,
            'type' => $attribute['type']
          ];
        }
      }
    }
    $productData['attributes'] = $_attributes;
    $productData['manufacturer_url'] = '';

    if (isset($productData['attributes']['manufacturer'])) {
      $manufacturerName = $productData['attributes']['manufacturer']['value'];
      $productData['manufacturer_url'] = $baseUrl . \Mage::getModel('catalog/product_url')->formatUrlKey($manufacturerName);
    }
  }

}
