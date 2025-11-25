<?php

namespace beco\yii\commands;

use Yii;
use yii\console\ExitCode;
use yii\console\Controller;
use beco\yii\modules\clients\TelegramClient;

class TelegramController extends Controller {

  public function actionSetWebhook($domain) {
    $tg = new TelegramClient();
    $res = $tg->setWebhook($domain);
    if($res->isOk) {
      echo "Webhook set successfully\n";
    } else {
      echo "Failed to set webhook\n";
    }
    return ExitCode::OK;
  }

  public function actionWebhookInfo() {
    $tg = new TelegramClient();
    $res = $tg->getWebhookInfo();
    if(!$res->isOk) {
      echo "Failed to get webhook info\n";
      return ExitCode::UNSPECIFIED_ERROR;
    }
    echo "Webhook info: \n";
    foreach($res->content['result'] as $key => $value) {
      echo $key . ": " . $value . "\n";
    }
    return ExitCode::OK;
  }
}
