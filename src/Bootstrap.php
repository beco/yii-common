<?php

namespace beco\yii;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface {

  public function bootstrap($app) {
    // Solo aplicar si es consola
    if ($app instanceof \yii\console\Application) {
      $app->controllerMap['system'] = [
        'class' => \beco\yii\commands\SystemController::class,
      ];
    }
  }
}
