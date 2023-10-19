<?php


namespace Disual\CustomApi\CustomService;

use Disual\CustomApi\Http\ErrorDocument;
use Disual\CustomApi\Http\JsonResponder;
use Disual\CustomApi\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

class CustomJsonResponder extends JsonResponder {

  public function error(int $error, string $argument, $details = null): ResponseInterface {
    $document = new ErrorDocument(new CustomErrorCode($error), $argument, $details);
    return ResponseFactory::fromObject($document->getStatus(), $document);
  }
}
