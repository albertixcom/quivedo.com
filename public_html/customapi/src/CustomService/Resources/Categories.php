<?php
/** @noinspection PhpUnused */

namespace Disual\CustomApi\CustomService\Resources;

use Disual\CustomApi\CustomService\CustomErrorCode;
use Disual\CustomApi\Http\RequestUtils;
use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class Categories extends ResourceBase {

  public function handle(ServerRequestInterface $request): ResponseInterface {
    $requestMethod = $request->getMethod();
    $segment = RequestUtils::getPathSegment($request, 3);
    if ($segment == 'tree') {
      return $this->categoriesTree($request);
    }

    if (strtoupper($requestMethod) == 'GET') {
      return $this->listCategories($request);
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $request->getUri()->getPath());
  }

  protected function listCategories(ServerRequestInterface $request): ResponseInterface {
    $params = $request->getQueryParams();

    $storeId = (isset($params['store_id'])) ? (int)$params['store_id'] : 0;
    $onlyActive = (isset($params['active']) && (int)$params['active'] == 1);

    $connector = new Magento1Connector();
    $categories = $connector->listCategories($storeId, $onlyActive);

    return $this->responder->success(['result' => 'success', 'total' => count($categories), 'categories' => $categories]);
  }

  protected function categoriesTree(ServerRequestInterface $request): ResponseInterface {
    $params = $request->getQueryParams();

    $storeId = (isset($params['store_id'])) ? (int)$params['store_id'] : 0;
    $onlyActive = (isset($params['active']) && (int)$params['active'] == 1);

    $connector = new Magento1Connector();
    $categories = $connector->categoriesTree($storeId, $onlyActive);

    return $this->responder->success(['result' => 'success', 'categories' => $categories]);
  }
}
