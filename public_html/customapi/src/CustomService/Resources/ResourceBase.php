<?php

namespace Disual\CustomApi\CustomService\Resources;

use Disual\CustomApi\CustomService\CustomJsonResponder;
use Disual\CustomApi\Core;
use Envms\FluentPDO\Query;
use Monolog\Logger;

class ResourceBase {

  /**
   * @var Query
   */
  protected Query $db;

  protected CustomJsonResponder $responder;

  /**
   * @var Logger
   */
  protected Logger $logger;

  public function __construct() {
    $this->db = Core\AppContext::getInstance()->getDB();
    $this->responder = new CustomJsonResponder(true);
    $this->logger = Core\AppLogger::getInstance()->getLogger();
  }

}
