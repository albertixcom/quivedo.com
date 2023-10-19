<?php /** @noinspection PhpUnused */

namespace Disual\CustomApi\Core;

class AppArgs {

  /**
   * Parametri obbligatori (uno di questi)
   */
  private static bool $runTest = false;

  /*
   * Setters/Getters
   */

  /**
   *
   * @param bool $runTest
   */
  public static function setRunTest(bool $runTest) {
    self::$runTest = $runTest;
  }

  /**
   *
   * @return bool
   */
  public static function getRunTest():bool {
    return self::$runTest;
  }



  /**
   *
   * @param array $options
   * @param boolean $exit
   */
  public static function usage(array $options, bool $exit = true) {

    // elencare lista dei parametri disponibili
    print_r($options);
    if ($exit) {
      die();
    }
  }

  /** @noinspection PhpUnused */
  public static function dumpArgs() {
    $out = " \n ========================================== "."\n";
    $out .= " - run-test: ".self::formatBool(self::$runTest)."\n";
    $out .= " ========================================== "."\n";

    echo("$out");
  }

  /**
   *
   */
  public static function parseArgs() {

    $longOptions = [];
    $shortOptions = [];

    $longOptions[] = "run-test";

    // --------- Inizio elaborazione
    $options = getopt(implode("", $shortOptions), $longOptions);

    if (array_key_exists("help", $options) || array_key_exists("h", $options)) {
      self::usage($options);
    }

    if (array_key_exists("run-test", $options)) {
      self::$runTest = true;
    }
  }

  static function formatBool($var = true): string {
    return ($var)?'Si':'No';
  }
}
