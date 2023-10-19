<?php
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpMissingFieldTypeInspection */


namespace Disual\CustomApi\Core;

trait Singleton {
  static private $instance = null;
  private function __construct() { /* ... @return Singleton */ } // Protect from creation through new Singleton
  private function __clone() { /* ... @return Singleton */ } // Protect from creation through clone
  //private function __wakeup() { /* ... @return Singleton */ } // Protect from creation through unserialize
  static public function getInstance() {
    return self::$instance===null
      ? self::$instance = new static()//new self()
      : self::$instance;
  }
}

//class AppCore {
//
//  private static $instance;
//
//  public static function getInstance() {
//    if (!isset(self::$instance)) {
//      self::$instance = new AppCore();
//    }
//
//    return self::$instances[static::class];
//}
//
//}
