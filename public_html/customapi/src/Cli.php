<?php

namespace Disual\CustomApi;

use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Exception;
use InvalidArgumentException;
use Disual\CustomApi\Core As AppCore;

class Cli {

  /**
   * App constructor.
   *
   * @throws Exception
   */
  public function __construct() {

    /* Argomenti riga di commando */
    AppCore\AppArgs::parseArgs();
  }

  public function dispatch() {

    $time = AppCore\AppUtils::getMicrotime();

    if (AppCore\AppArgs::getRunTest()) {

      try {
        AppCore\AppLogger::getInstance()->setPrefixPath("run-test");
      } catch (Exception $e) {
      }

      $this->runTest();
    } else {
      throw new InvalidArgumentException("parametri mancanti");
    }

    $elapsed = AppCore\AppUtils::getMicrotime() - $time;
    $message = "\n-------------------------------\n"
      ."\$elapsed: $elapsed\n"
      ."-------------------------------\n";

    echo $message;
  }

  protected function runTest() {
    echo " ***** TEST ******\n";
    $connector = new Magento1Connector();
    $connector->getRandomProducts();



    $result = $connector->getProductExtended('SP0068 02N');
    print_r($result);
  }
}
