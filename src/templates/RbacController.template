<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;


class RbacController extends Controller {

  public function actionInit() {
    $auth = Yii::$app->authManager;

    if(empty($auth)) {
      die("no auth configured\n");
    }

    $auth->removeAll();

    /*
    $permission = $auth->createPermission('permissionName');
    $auth->add($permission);

    $role = $auth->createRole('role');
    $auth->add($role);
    $auth->addChild($role, $permission);

    $admin = $auth->createRole('admin');
    $auth->add($admin);
    $auth->addChild($admin, $user);
    $auth->addChild($admin, $permission);


    $users = User::find()->all();

    foreach($users as $u) {
      if($u->is_admin == 1) {
        $auth->assign($admin, $u->id);
      } else {
        $auth->assign($user, $u->id);
      }
    }
    */

    return ExitCode::OK;
  }
}
