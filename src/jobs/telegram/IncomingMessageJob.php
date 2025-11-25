<?php

namespace beco\yii\jobs\telegram;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use beco\yii\modules\clients\TelegramClient;

class IncomingMessageJob extends BaseObject implements JobInterface {
  public $message_id;

  public function execute($queue) {
    Yii::info(['msg' => 'handle incoming message', 'extra' => ['message' => $message]]);
  }
}
