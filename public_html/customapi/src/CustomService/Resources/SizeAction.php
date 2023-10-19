<?php

/** @noinspection PhpUnused */

namespace Disual\CustomApi\CustomService\Resources;

use Disual\CustomApi\CustomService\CustomErrorCode;
use Disual\CustomApi\Magento1Connector\Magento1Connector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class SizeAction extends ResourceBase {

  public function handle(ServerRequestInterface $request): ResponseInterface {
    $requestMethod = $request->getMethod();
    if (strtoupper($requestMethod) == 'POST') {
      return $this->updateSize($request);
    }
    return $this->responder->error(CustomErrorCode::ROUTE_NOT_FOUND, $request->getUri()->getPath());
  }

  private function updateSize(ServerRequestInterface $request): ResponseInterface {
    $_postData = json_decode(json_encode($request->getParsedBody(), JSON_FORCE_OBJECT), true);

    $this->logger->debug("\$_postData:\n".print_r($_postData, true)."\n");

    $sku = ($_postData['sku'])??'';
    $size = ($_postData['size'])??'';
    $action = ($_postData['action'])??'';

    $connector = new Magento1Connector();
    $result = $connector->updateSize($sku, $size, $action);

    $this->logger->debug("call::updateSize \$result [$result]");

    if ($result) {
      return $this->responder->success(['result' => 'success']);
    } else {

      return $this->responder->success(['result' => 'error']);
    }
  }

}
