<?php

namespace beco\yii\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class System extends Controller {

  /**
   * Prints this system's to connect it's db.
   */
  public function actionDb() {
    printf("> mysql -u %s -p -h %s --database=%s\n\n%s\n",
      getenv('db_user'),
      getenv('db_host'),
      getenv('db_name'),
      getenv('db_pass')
    );
    return ExitCode::OK;
  }

  /**
   * Simple healthcheck.
   *
   * ./yii system/ping
   */
  public function actionPing(): int
    {
        $this->stdout("pong\n");
        return ExitCode::OK;
    }

    /**
     * Muestra información básica del entorno de la app.
     *
     * ./yii system/info
     */
    public function actionInfo(): int
    {
        $this->stdout("App ID: " . Yii::$app->id . "\n");
        $this->stdout("Environment: " . (defined('YII_ENV') ? YII_ENV : 'unknown') . "\n");
        $this->stdout("Debug: " . (defined('YII_DEBUG') && YII_DEBUG ? 'true' : 'false') . "\n");
        $this->stdout("Base Path: " . Yii::getAlias('@app') . "\n");

        return ExitCode::OK;
    }
}
