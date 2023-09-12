<?php

namespace Disual\StaticProducts\Core;


class AppUtils {


  public static function searchInArrayEx($value, $array, $column, $caseSensitive = true): ?array {
    if (!$caseSensitive) {
      $value = strtoupper($value);
    }
    foreach ($array As $item) {
      $term = $item[$column];
      if (!$caseSensitive) {
        $term = strtoupper($term);
      }
      if ($term == $value) {
        return $item;
      }
    }
    return null;
  }


}
