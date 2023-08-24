<?php

  require_once __DIR__ . '/vendor/autoload.php';

  define("APP_ROOT", dirname(__FILE__));

  use Disual\StaticProducts\{App,Cli};

if(php_sapi_name() == "cli") {
  //In cli-mode
  $cli = new Cli();
  $cli->dispatch();
} else {
  $app = new App();
  $app->dispatch();
}

