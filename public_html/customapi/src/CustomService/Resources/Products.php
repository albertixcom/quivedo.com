<?php
/** @noinspection PhpUnused */

namespace Disual\CustomApi\CustomService\Resources;


use Disual\CustomApi\CustomService\CustomErrorCode;
use Disual\CustomApi\Http\RequestUtils;
use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class Products extends ResourceBase {

  protected Magento1Connector $connector;

  public function __construct() {
    parent::__construct();
    $this->connector = new Magento1Connector();

  }

  public function handle(ServerRequestInterface $request): ResponseInterface {

    $requestMethod = $request->getMethod();
    if (strtoupper($requestMethod) == 'GET') {

      return $this->getProductExtended($request);
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $request->getUri()->getPath());
  }

  protected function getProductExtended(ServerRequestInterface $request): ResponseInterface {

    $rawSku = RequestUtils::getPathSegment($request, 3);
    $params = $request->getQueryParams();
    $storeId = (!empty($params) && isset($params['store_id']))?(int)$params['store_id']:0;
    $sku = urldecode($rawSku);
    // replace con _ /
    $sku = preg_replace('#_#', '/', $sku);

    $_product = $this->connector->getProductExtended($sku, $storeId);
    $product = ($_product)??[];
    return $this->responder->success(['result' => 'success', 'product' => $product]);
  }
}
