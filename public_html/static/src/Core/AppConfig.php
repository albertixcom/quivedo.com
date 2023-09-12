<?php

namespace Disual\StaticProducts\Core;

use Exception;

/**
 * Configurazione
 *
 * @author albertix
 */
class AppConfig {

  use Singleton;

  const DEFAULT_LANG = 'it';
  const LANGS_X = [
    'it' => [
      'name' => 'ITA',
      'intl_code' => 'it-IT',
      'store_id' => 1
    ],
    'en' => [
      'name' => 'ENG',
      'intl_code' => 'en-US',
      'store_id' => 2
    ],
    'nl' => [
      'name' => 'NED',
      'intl_code' => 'nl-NL',
      'store_id' => 4
    ]
  ];

  const BASE_URL = 'https://www.quivedo.com/';

  /**
   *
   * @var array
   */
  protected array $config;

  /**
   * AppConfig constructor.
   *
   * @throws Exception
   */
  private function __construct() {
    $this->loadConfig();
  }

  /**
   * @return array
   */
  public function getConfig(): array {
    return $this->config;
  }

  /**
   * @throws Exception
   */
  private function loadConfig() {

    $configPath = APP_ROOT."/config";

    //
    // Config
    //
    $configFile = $configPath."/config.json";

    if (!file_exists($configFile)) {
      throw new Exception("File config inesistente: $configFile\n");
    }
    $content = file_get_contents($configFile);
//    echo "----------------------------------\n";
//    echo $content . "\n";
//    echo "----------------------------------\n";

    $config = json_decode($content, true);
    $this->config = $config;
  }

}
