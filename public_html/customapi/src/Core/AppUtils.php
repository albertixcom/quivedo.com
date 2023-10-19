<?php

namespace Disual\CustomApi\Core;


use DateTime;
use Exception;

class AppUtils {

  /**
   * @param string $url
   * @param array $payloadData
   * @param array $headers
   * @return bool|string
   */
  public static function postRequest(string $url, array $payloadData, array $headers = []) {

    $result = false;
    try {
      // Converte array nel formato JSON
      $payload = json_encode($payloadData);

      // Inizio sessione curl
      $ch = curl_init($url);

      // Resituisce risultato della chiamata
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Ottiene informazioni riguardo ultima chiamata
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);

      // Invio dati con POST
      curl_setopt($ch, CURLOPT_POST, true);

      // Impostazione del payload della chiamata (dati)
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

      // Dato che si tratta della chiamata json, vengono impostate delle intestazioni
      $requestHeaders = [
        // 'X-API-KEY: '.self::MOVEMENT_API_KEY,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
      ];
      if (!empty($headers)) {
        $requestHeaders = array_merge($requestHeaders, $headers);
      }

      curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);

      // altre opzioni che si possono implementare
      // -- previene verifica certificato ssl (potrebbe rallentare)
//      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      // -- ci deve impegnare al massimo 5 sec
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);

      // Esecuzione
      $result = curl_exec($ch);

      // Chiusura della sessione
      curl_close($ch);
    } catch (Exception $ex) {
      // qui sarebbe da eseguire log
      $logger = AppLogger::getInstance()->getLogger();
      $logger->error("!!! Errore:\n".print_r($ex,true)."\n");
    }

    return $result;
  }

  // ============= DATE FUNCTIONS ==============


  public static function getMicrotime() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
  }

  /**
   * Covert string with dashes into camel-case string.
   *
   * @param string $string A string with dashes.
   *
   * @return string
   * @noinspection PhpUnused
   */
  public static function getCamelCase(string $string = ''): string {
    $str = explode('-', $string);
    return implode('', array_map(function($word) {
      return ucwords($word);
    }, $str));
  }

  /**
   * @param string $text
   * @return string
   * @noinspection PhpUnused
   */
  public static function slugString(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
  }


  /** @noinspection PhpUnused */
  public static function dateTimeToMilliseconds(DateTime $dateTime) {
    $secs = $dateTime->getTimestamp(); // Gets the seconds
    $millisecs = $secs*1000; // Converted to milliseconds
    $millisecs += $dateTime->format("u")/1000; // Microseconds converted to seconds
    return $millisecs;
  }


  // ============= MISC FUNCTIONS ==============

  /**
   *
   * @noinspection PhpUnused
   */
  public static function camelize($str, $sep = "_"): string {
    // camelizze
    $_words = preg_split('/'.$sep.'/', $str);
    $words = [];
    foreach ($_words as $word) {
      $words[] = ucfirst($word);
    }
    return implode("", $words);
  }

  /** @noinspection PhpUnused */
  public static function sanitizeFilename($fileName, $defaultIfEmpty = 'default', $separator = '_', $lowerCase = true): string {
    // Gather file informations and store its extension
    $fileInfos = pathinfo($fileName);
    $fileExt   = array_key_exists('extension', $fileInfos) ? '.'. strtolower($fileInfos['extension']) : '';

    // Removes accents
    $fileName = @iconv('UTF-8', 'us-ascii//TRANSLIT', $fileInfos['filename']);

    // Removes all characters that are not separators, letters, numbers, dots or whitespaces
    $fileName = preg_replace("/[^ a-zA-Z". preg_quote($separator). "\d.\s]/", '', $lowerCase ? strtolower($fileName) : $fileName);

    // Replaces all successive separators into a single one
    $fileName = preg_replace('!['. preg_quote($separator).'\s]+!u', $separator, $fileName);

    // Trim beginning and ending seperators
    $fileName = trim($fileName, $separator);

    // If empty use the default string
    if (empty($fileName)) {
      $fileName = $defaultIfEmpty;
    }

    return $fileName. $fileExt;
  }

  /** @noinspection PhpUnused */
  public static function generateSeoURL(string $string, $wordLimit = 0): string {
    $separator = '-';

    if($wordLimit != 0){
      $wordArr = explode(' ', $string);
      $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
    }

    $quoteSeparator = preg_quote($separator, '#');

    $trans = array(
      '&.+?;'                    => '',
      '[^\w\d _-]'            => '',
      '\s+'                    => $separator,
      '('.$quoteSeparator.')+'=> $separator
    );

    $string = strip_tags($string);
    foreach ($trans as $key => $val){
      $string = preg_replace('#'.$key.'#i', $val, $string);
    }

    $string = strtolower($string);

    return trim(trim($string, $separator));
  }


  public static function RandomStr($type = 'uppernum', $length = 8)
  {
    switch($type)
    {
      case 'basic'    : return mt_rand();
      case 'alpha'    :
      case 'alphanum' :
      case 'num'      :
      case 'nozero'   :
      case 'uppernum' :
        $seedings             = array();
        $seedings['alpha']    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seedings['alphanum'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seedings['num']      = '0123456789';
        $seedings['nozero']   = '123456789';
        $seedings['uppernum']   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $pool = $seedings[$type];

        $str = '';
        for ($i=0; $i < $length; $i++)
        {
          $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
        }
        return $str;
      case 'unique'   :
      case 'md5'      :
        return md5(uniqid(mt_rand()));
    }

    return '';
  }

  /** @noinspection PhpUnused */
  public static function fromBase36(string $number): string {
    return base_convert ( $number , 36 , 10 );
  }

  /** @noinspection PhpUnused */
  public static function toBase36(string $number): string {
    return base_convert ( $number , 10 , 36 );
  }

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
