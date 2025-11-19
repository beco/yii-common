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
}
