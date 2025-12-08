<?php
// components/DebugModule.php
namespace app\components;

use Yii;

class DebugModule extends \yii\debug\Module {

    // Keep permissive so our own check decides:
  public $allowedIPs = ['*'];

  public function init() {
    parent::init();
    // Force using the original yii2-debug view path
    $this->setViewPath('@yii/debug/views');
  }

  public function checkAccess($action = null) {
    // Only signed-in users with a specific permission may see Debug
    return !Yii::$app->user->isGuest && Yii::$app->user->can('admin');
  }
}
