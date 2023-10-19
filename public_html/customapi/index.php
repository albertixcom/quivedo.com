<?php


  require_once __DIR__ . '/vendor/autoload.php';

  define("APP_ROOT", dirname(__FILE__));

  use Disual\CustomApi\{Api,Cli};

  $app = (php_sapi_name() == "cli") ? new Cli() : new Api();
  $app->dispatch();


