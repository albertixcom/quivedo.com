<?php

namespace Disual\StaticProducts\Core;


class AppUtils {


  public static function searchInArrayEx($value, $array, $column): ?array {
    foreach ($array As $item) {
      if ($item[$column] == $value) {
        return $item;
      }
    }
    return null;
  }


}
