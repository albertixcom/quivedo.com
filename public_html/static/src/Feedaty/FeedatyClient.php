<?php


namespace Disual\StaticProducts\Feedaty;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class FeedatyClient {
  public static array $endpoints = [
    'GetProductsRating' => [
      'method' => 'GET',
      'action' => 'GetProductsRating',
      'url' => 'https://widget.zoorate.com/go.php?function=feed&action=ws&task=product'
    ]
  ];

  private array $config = [
    'endpoint' => null,
    'merchantCode' => null,
    'clientSecret' => null,
    'user-agent' => "PHP/7.4"
  ];
  /*
   * "endpoint": "https://orwell.disual.it/admintools",
    "apikey": "64606fba-ed76-11ec-8ea0-0242ac120002"
   */

  protected ?Client $client = NULL;

  /// --------------------- ENDPOINTS



  /**
   * @return array|null
   * @noinspection PhpUnused
   */
  public function GetProductsRating(): ?array {
    try {
      return $this->request(
        'GetProductsRating'
      );
    } catch (Exception $e) {
      echo "ERROR > ".$e->getMessage()."\n";
      return null;
    }
  }

  /// ------------------------------

  public function __construct($config = []) {
    foreach($config as $key => $value) {
      if (array_key_exists($key, $this->config)) {
        $this->config[$key] = $value;
      }
    }
    $this->client = new Client();
  }


  /**
   * @throws Exception
   */
  public static function get($key): array {
    if (isset(self::$endpoints[$key])) {
      return self::$endpoints[$key];
    } else {
      throw new Exception('Call to undefined endpoint ' . $key);
    }
  }

  /**
   * @throws Exception
   * @noinspection PhpSameParameterValueInspection
   */
  private function request($endPoint, array $params = [], array $query = [], $body = null, $raw = false) {

    $endPoint = self::get($endPoint);

    try{

      $headers = [
        'Accept' => 'application/json',
        'User-Agent' => $this->config['user-agent'],
      ];

      $requestOptions = [
        'headers' => $headers,
        'body' => $body,
        RequestOptions::VERIFY  => false,
        //"debug" => true,
      ];

      ksort($query);

      $requestOptions['query'] = $query;

      if($this->client === NULL) {
        $this->client = new Client();
      }

      $url = $endPoint['path'];
      if (!empty($params)) {
        foreach ($params As $key => $value) {
          $url = str_replace('{'.$key.'}', $value, $url);
        }
      }

      if (isset($endPoint['url'])) {
        $url = '';
        $this->config['endpoint'] = $endPoint['url'].'&merchant_code='.$this->config['merchantCode'];
        unset($requestOptions['query']);
        //unset($requestOptions['headers']);
      }

      $response = $this->client->request(
        $endPoint['method'],
        $this->config['endpoint'] . $url,
        $requestOptions
      );

      $body = (string) $response->getBody();

      if ($raw) {
        return $body;
      } else if (strpos(strtolower($response->getHeader('Content-Type')[0]), 'json') !== false) {
        return json_decode($body, true);
      } else {
        return $body;
      }

    } catch (BadResponseException $e) {
      if ($e->hasResponse()) {
        $message = $e->getResponse();
        $message = $message->getBody();
        if (strpos($message, '<ErrorResponse') !== false) {
          $error = simplexml_load_string($message);
          $message = $error->Error->Message;
        }
      } else {
        $message = 'An error occured';
      }
      throw new Exception($message);
    } catch (GuzzleException $e) {
      throw new Exception('An error occured');
    }
  }
}
