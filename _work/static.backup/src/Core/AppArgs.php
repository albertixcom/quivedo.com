<?php /** @noinspection PhpUnused */

/*
 * Esempio
 *
 */
namespace Disual\StaticProducts\Core;

class AppArgs {

  /**
   * Parametri obbligatori (uno di questi)
   */
  private static bool $runTest = false;
  private static bool $buildSitemap = false;

  /*
   * Setters/Getters
   */

  public static function setBuildSitemap(string $buildSitemap) { self::$buildSitemap = $buildSitemap; }
  public static function getBuildSitemap():string { return self::$buildSitemap; }




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

    $out .= " - build-sitemap: ".self::formatBool(self::$buildSitemap)."\n";
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
    $longOptions[] = "build-sitemap";

    $shortOptions[] = "csv:";

    // --------- Inizio elaborazione
    $options = getopt(implode("", $shortOptions), $longOptions);

    if (array_key_exists("help", $options) || array_key_exists("h", $options)) {
      self::usage($options);
    }

    if (array_key_exists("run-test", $options)) {
      self::$runTest = true;
    } elseif (array_key_exists("build-sitemap", $options)) {
      self::$buildSitemap = true;
    }
  }

  static function formatBool($var = true): string {
    return ($var)?'Si':'No';
  }

  static function formatArr($var = []) {
    return json_encode($var);
  }
}
