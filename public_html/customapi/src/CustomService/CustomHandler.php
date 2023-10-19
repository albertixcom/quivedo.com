<?php /** @noinspection PhpMultipleClassDeclarationsInspection */


namespace Disual\CustomApi\CustomService;

use Disual\CustomApi\Core\AppConfig;
use Disual\CustomApi\CustomService\Resources;
use Disual\CustomApi\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Disual\CustomApi\Http\RequestUtils;

class CustomHandler {

  private CustomJsonResponder $responder;

  public function __construct() {
    $this->responder = new CustomJsonResponder(true);
  }

  /**
   * @param ServerRequestInterface $request
   * @return ResponseInterface
   */
  public function handle(ServerRequestInterface $request) {

    $method = $request->getMethod();
    if ($method == 'OPTIONS') {
      return $this->handleOptions();
    }

    $result = $this->authMiddleware($request);
    if ($result !== true) {
      return $result;
    }

    $routes = [
      'products' => Resources\Products::class,
      'categories' => Resources\Categories::class,
      'product-update' => Resources\ProductUpdate::class,
      'size-action' => Resources\SizeAction::class,
      'stock-movement' => Resources\StockMovement::class
    ];

    $route = RequestUtils::getPathSegment($request, 2);

    if (array_key_exists($route, $routes)) {
      $class = new $routes[$route];
      $methodName = RequestUtils::getPathSegment($request, 3);

      if (!empty($methodName) && method_exists($class, $methodName)) {
        return $class->$methodName($request);
      }

      if (method_exists($class, 'handle')) {
        return $class->handle($request);
      }
    }

    // richieste interne
    if ($route == 'custom') {
      $methodName = RequestUtils::getPathSegment($request, 3);
      if (!empty($methodName) && method_exists($this, $methodName)) {
        /* @var $response ResponseInterface */
        return $this->$methodName($request);
      }
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $route);
  }

  private function handleOptions(): ResponseInterface {
    $response = ResponseFactory::fromStatus(ResponseFactory::OK);
    $response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-XSRF-TOKEN, X-Authorization, X-API-Key, X-ACCOUNT-ID, X-APP-VERSION');
    $response = $response->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, PUT, POST, DELETE, PATCH');
    $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
    $response = $response->withHeader('Access-Control-Max-Age', '1728000');
    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    $response = $response->withHeader('Access-Control-Expose-Headers', '');

    return $response;
  }

  private function authMiddleware(ServerRequestInterface $request) {

    $apiKey = RequestUtils::getHeader($request, 'X-API-Key');
    if ($apiKey) {
      $identities = AppConfig::getInstance()->getConfig()['identities'];
      $apiKeys = array_keys($identities);
      if (!in_array($apiKey, $apiKeys)) {
        return $this->responder->error(CustomErrorCode::AUTHENTICATION_FAILED, $apiKey);
      }
    } else {
      return $this->responder->error(CustomErrorCode::AUTHENTICATION_REQUIRED, '');
    }
    return true;
  }


}
