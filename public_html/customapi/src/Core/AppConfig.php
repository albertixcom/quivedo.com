<?php

namespace Disual\CustomApi\Core;

use Exception;

/**
 * Configurazione
 *
 * @author albertix
 */
class AppConfig {

  use Singleton;

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
    $configFile = $configPath."/app_config.json";

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
