<?php
namespace Disual\StaticProducts\I18N;


use Disual\StaticProducts\Core\AppContext;

class Lang {


  protected static array $texts = [];

  private static function loadLang(string $lang) {
    $langFile = dirname(__FILE__) . "/".$lang.".json";
    if (file_exists($langFile)) {
      $texts = json_decode(file_get_contents($langFile), true);
      self::$texts = $texts;
    }
  }

  public static function is(string $key, string $group = ''):bool {
    if (empty(self::$texts)) {
      self::loadLang(AppContext::$curLang);
    }
    if (!empty($group) && isset(self::$texts[$group]) && key_exists($key, self::$texts[$group])) {
      return true;
    }

    if (key_exists($key, self::$texts)) {
      return true;
    }
    return false;
  }

  public static function l(string $key, string $group = '') {
    if (empty(self::$texts)) {
      self::loadLang(AppContext::$curLang);
    }

    if (!empty($group) && isset(self::$texts[$group]) && key_exists($key, self::$texts[$group])) {
      return self::$texts[$group][$key];
    }

    if (key_exists($key, self::$texts)) {
      return self::$texts[$key];
    }
    return $key;
  }



}
