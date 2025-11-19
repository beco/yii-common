<?php

namespace beco\yii;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Bootstrap implements BootstrapInterface {

  public function bootstrap($app) {
    // Solo aplicar si es consola
    if ($app instanceof ConsoleApplication) {
      $app->controllerMap['system'] = [
        'class' => \beco\yii\commands\SystemController::class,
      ];
    }
  }
}
