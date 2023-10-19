<?php

/** @noinspection PhpUnused */

namespace Disual\CustomApi\CustomService\Resources;

use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Disual\CustomApi\CustomService\CustomErrorCode;

class StockMovement extends ResourceBase {

  public function handle(ServerRequestInterface $request): ResponseInterface {
    $requestMethod = $request->getMethod();
    if (strtoupper($requestMethod) == 'POST') {
      return $this->updateStock($request);
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $request->getUri()->getPath());
  }

  private function updateStock(ServerRequestInterface $request): ResponseInterface {
    $_postData = json_decode(json_encode($request->getParsedBody(), JSON_FORCE_OBJECT), true);
    $sku = ($_postData['sku'])??'';
    $movement = ($_postData['movement'])??0;
    $sizes = ($_postData['sizes'])??[];

    $this->logger->debug("\$_postData:\n".print_r($_postData, true)."\n");
    $this->logger->debug("\$sizes:\n".print_r($sizes, true)."\n");

    $this->logger->debug("call::updateStock");

    $connector = new Magento1Connector();
    $result = $connector->updateStock($sku, $movement, $sizes);

    $this->logger->debug("call::updateStock \$result [$result]");

    if ($result) {
      return $this->responder->success(['result' => 'success']);
    } else {

      return $this->responder->success(['result' => 'error']);
    }
  }

}
