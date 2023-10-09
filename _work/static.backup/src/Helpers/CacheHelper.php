<?php


namespace Disual\StaticProducts\Helpers;


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
}
