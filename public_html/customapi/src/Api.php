<?php /** @noinspection PhpMultipleClassDeclarationsInspection */


namespace Disual\CustomApi;


use Disual\CustomApi\CustomService\CustomHandler;
use Disual\CustomApi\Http\RequestFactory;
use Disual\CustomApi\Http\ResponseUtils;

class Api {

  public function dispatch() {

    $request = RequestFactory::fromGlobals();

    $handler = new CustomHandler();
    $response = $handler->handle($request);
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    ResponseUtils::output($response);

  }

}
