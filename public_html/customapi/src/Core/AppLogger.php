<?php

  namespace Disual\CustomApi\Core;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;

class AppLogger {

  use Singleton;

  /**
   *
   * @var Logger
   */
  protected Logger $log;

  protected string $prefixPath = "";

  /**
   * AppLogger constructor.
   *
   * @throws Exception
   */
  private function __construct() {
    $this->setup();
  }

  /**
   * @throws Exception
   */
  private function setup() {

    // create a log channel
    $log = new Logger('Albertix\CustomApi');

    // const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    $this->createHandlers(Logger::DEBUG, $log);
    $this->createHandlers(Logger::ERROR, $log);

    $this->log = $log;
  }

  /**
   * @param int $level
   * @param Logger $log
   * @throws Exception
   * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
   */
  private function createHandlers(int $level, Logger &$log) {

    $fileName = "debug.log";
    switch ($level) {
      case Logger::DEBUG:
        $fileName = "debug.log";
        break;
      case Logger::ERROR:
        $fileName = "error.log";
    }

    //echo $this->getLogsPath().'/'.$fileName;

    $fileHandler = new StreamHandler($this->getLogsPath().'/'.$fileName, $level);
    $fileHandler->setFormatter(new LineFormatter(null,null,true,true));
    $log->pushHandler($fileHandler);

    $processor = new IntrospectionProcessor($level);
    $log->pushProcessor($processor);

  }

  private function getLogsPath(): string {
    $logsPath = APP_ROOT.'/logs/'.date("Y-m-d");
    if (!empty($this->prefixPath)) {
      $logsPath = APP_ROOT.'/logs/'.$this->prefixPath.'/'.date("Y-m-d");
      if (!is_dir($logsPath)) { mkdir($logsPath, 0755, true); }
    }
    return $logsPath;
  }

  /**
   * @param $prefixPath
   * @throws Exception
   */
  public function setPrefixPath($prefixPath) {
    $this->prefixPath = $prefixPath;
    $this->setup();
  }

  /**
   *
   * @return Logger
   */
  public function getLogger(): Logger {
    return $this->log;
  }

}

