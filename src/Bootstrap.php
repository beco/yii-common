<?php

namespace beco\yii;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

class Bootstrap implements BootstrapInterface {

  public function bootstrap($app) {
    // Solo aplicar si es consola
    if ($app instanceof ConsoleApplication) {
      $app->controllerMap['project'] = [
        'class' => \beco\yii\commands\ProjectController::class,
      ];
      $app->controllerMap['telegram'] = [
        'class' => \beco\yii\commands\TelegramController::class,
      ];
      $app->controllerMap['beat'] = [
        'class' => \beco\yii\commands\BeatController::class,
      ];
    }
  }
}
