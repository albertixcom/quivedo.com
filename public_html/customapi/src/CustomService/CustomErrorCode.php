<?php /** @noinspection PhpUnused */


namespace Disual\CustomApi\CustomService;


use Disual\CustomApi\Http\ErrorCode;
use Disual\CustomApi\Http\ResponseFactory;

class CustomErrorCode extends ErrorCode {

  const ERROR_EMAIL_REQUIRED              = 10000;
  const ERROR_DB                          = 10900;
  const ERROR_GENERAL                     = 10902;

  private array $errorsMap = [
    10000 => ["Email required", ResponseFactory::NOT_FOUND],
    10900 => ["Server error. Database error. %s", ResponseFactory::INTERNAL_SERVER_ERROR],
    10902 => ["Server error. %s", ResponseFactory::INTERNAL_SERVER_ERROR],

  ];

  public function __construct(int $code)
  {
    foreach ($this->errorsMap As $k => $v) {
      $this->values[$k] = $v;
    }
    parent::__construct($code);
  }
}
