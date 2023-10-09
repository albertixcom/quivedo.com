<?php
namespace Disual\StaticProducts\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestUtils  {

  /** @noinspection PhpUnused */
  public static function setParams(ServerRequestInterface $request, array $params): ServerRequestInterface {
    $query = preg_replace('|%5B[0-9]+%5D=|', '=', http_build_query($params));
    return $request->withUri($request->getUri()->withQuery($query));
  }

  public static function getHeader(ServerRequestInterface $request, string $header): string {
    $headers = $request->getHeader($header);
    return $headers[0] ?? '';
  }

  /** @noinspection PhpUnused */
  public static function getParams(ServerRequestInterface $request): array {
    $params = array();
    $query = $request->getUri()->getQuery();
    //$query = str_replace('][]=', ']=', str_replace('=', '[]=', $query));
    $query = str_replace('%5D%5B%5D=', '%5D=', str_replace('=', '%5B%5D=', $query));
    parse_str($query, $params);
    return $params;
  }

  public static function getPathSegment(ServerRequestInterface $request, int $part): string {
    $path = $request->getUri()->getPath();
    $pathSegments = explode('/', rtrim($path, '/'));
    if ($part < 0 || $part >= count($pathSegments)) {
      return '';
    }
    return urldecode($pathSegments[$part]);
  }

  /** @noinspection PhpUnused */
  public static function toString(ServerRequestInterface $request): string {
    $method = $request->getMethod();
    $uri = $request->getUri()->__toString();
    $headers = $request->getHeaders();
    $request->getBody()->rewind();
    $body = $request->getBody()->getContents();

    $str = "$method $uri\n";
    foreach ($headers as $key => $values) {
      foreach ($values as $value) {
        $str .= "$key: $value\n";
      }
    }
    if ($body !== '') {
      $str .= "\n";
      $str .= "$body\n";
    }
    return $str;
  }
}
