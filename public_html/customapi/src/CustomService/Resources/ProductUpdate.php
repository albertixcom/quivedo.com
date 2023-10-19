<?php

/** @noinspection PhpUnused */

namespace Disual\CustomApi\CustomService\Resources;

use Disual\CustomApi\CustomService\CustomErrorCode;
use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ProductUpdate extends ResourceBase {

  public function handle(ServerRequestInterface $request): ResponseInterface {
    $requestMethod = $request->getMethod();
    if (strtoupper($requestMethod) == 'POST') {
      return $this->updateProduct($request);
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $request->getUri()->getPath());
  }

  private function updateProduct(ServerRequestInterface $request): ResponseInterface {
    $_postData = json_decode(json_encode($request->getParsedBody(), JSON_FORCE_OBJECT), true);
    $sku = ($_postData['sku'])??'';
    $data = $_postData['data'];

    $this->logger->debug("\$_postData:\n".print_r($_postData, true)."\n");

    $this->logger->debug("call::updateProduct");

    $connector = new Magento1Connector();
    $result = $connector->updateProductData($sku, $data);

    $this->logger->debug("call::updateProduct \$result [$result]");

    if ($result) {
      return $this->responder->success(['result' => 'success']);
    } else {

      return $this->responder->success(['result' => 'error']);
    }
  }

}
