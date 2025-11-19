<?php

namespace beco\yii\db\exceptions;

use Exception;

class ModelNotSaved extends Exception {

  public function __construct($message = '', $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
