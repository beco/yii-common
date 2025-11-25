<?php

namespace beco\yii\interfaces;

use Yii;

interface HandlerInterface {
  public function canHandle(FlowContext $context):bool;
  public function execute(FlowContext $context):HandlerResult;
}
