<?php


namespace Disual\StaticProducts\Orwell;


use Disual\StaticProducts\Core\AppConfig;

class OrwellHelper {


  public static function fetchRemoteData(string $sku = null, string $url = null) {
    $config = AppConfig::getInstance()->getConfig()['orwell'];
    if ($url) {
      $result = (new OrwellClient($config))->GetProductExtended(null, urlencode($url));
    } else {
      $result = (new OrwellClient($config))->GetProductExtended(urlencode($sku));
    }

    if ($result && isset($result['data']) && !empty($result['data'])) {
      /** @noinspection PhpUnnecessaryLocalVariableInspection */
      $productData = $result['data'];
      return $productData;
    }
    return null;
  }



}
