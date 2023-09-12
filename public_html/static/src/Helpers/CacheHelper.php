<?php
/** @noinspection PhpUndefinedClassInspection */

namespace Disual\StaticProducts\Helpers;

use Disual\StaticProducts\Core\AppUtils;
use Mage;
use Mage_Catalog_Model_Product;
use Disual\StaticProducts\Core\AppConfig;

class CacheHelper {

  const CACHE_PATH = APP_ROOT . "/../static_cache";

  public static function urlFromCache(string $path): string {
    return AppConfig::BASE_URL . "static_cache" . $path;
  }

  /**
   * @param string $path
   * @return string
   */
  public static function getCachePath(string $path): string {
    $cachePath = self::CACHE_PATH . $path;
    if (!is_dir($cachePath)) {
      mkdir($cachePath, 0755, true);
    }
    return $cachePath;
  }

  public static function getAttributeValuesRaw(string $attributName, string $lang = AppConfig::DEFAULT_LANG):?array {
    $cachePath = self::CACHE_PATH . "/attributes";
    $storeId = 0;
    if (!is_dir($cachePath)) {
      mkdir($cachePath, 0755, true);
    }
    $cacheFile = $cachePath . "/attributes_".$attributName.".json";
    if ($lang!=AppConfig::DEFAULT_LANG) {
      $cacheFile = $cachePath . "/attributes_".$attributName."_".$lang.".json";
      if (isset(AppConfig::LANGS_X[$lang])) {
        $storeId = AppConfig::LANGS_X[$lang]['store_id'];
      }
    }

//    //@@@
//    if (file_exists($cacheFile)) {
//      unlink($cacheFile);
//    }

    if (!file_exists($cacheFile)) {
      try {
        $attribute = Mage::getModel('eav/config')->getAttribute(\Mage_Catalog_Model_Product::ENTITY, $attributName);
        if ($storeId>0) {
          $options = $attribute->setStoreId($storeId)->getSource()->getAllOptions();
        } else {
          $options = $attribute->getSource()->getAllOptions();
        }

        file_put_contents($cacheFile, json_encode($options));
        return $options;
      } catch (\Exception $ex) {
        return null;
      }
    } else {
      file_get_contents($cacheFile);
      return json_decode(file_get_contents($cacheFile), true);
    }
  }
}
