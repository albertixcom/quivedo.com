<?php /** @noinspection PhpUnused */

namespace Disual\CustomApi\I18N;


class Lang {


  protected static array $texts = [];

  /** @noinspection PhpSameParameterValueInspection */
  private static function loadLang(string $lang) {
    $langFile = dirname(__FILE__) . "/".$lang.".json";
    if (file_exists($langFile)) {
      $texts = json_decode(file_get_contents($langFile), true);
      self::$texts = $texts;
    }
  }

  public static function l(string $key) {
    if (empty(self::$texts)) {
      self::loadLang('pl');
    }
    if (key_exists($key, self::$texts)) {
      return self::$texts[$key];
    }
    return $key;
  }

  /**
   * @param string $filename
   * @param string $outDir
   * @noinspection PhpUnused
   */
  public function langFileToCSV(string $filename, string $outDir) {
    $json = file_get_contents($filename);
    $arr = json_decode($json, true);
    $targetFile = $outDir . "/" . pathinfo($filename, PATHINFO_BASENAME).".csv";
    echo "\$filename [$filename], \$outDir [$outDir], \$targetFile [$targetFile]\n";
    $fp = fopen($targetFile, 'w');
    foreach ($arr as $key => $value) {
      $fields = [$key, $value];
      fputcsv($fp, $fields);
    }
    fclose($fp);
    echo "DONE\n";
  }

}
