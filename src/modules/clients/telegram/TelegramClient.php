<?php

namespace beco\yii\modules\clients\telegram;

use Yii;
use Exception;
use yii\httpclient\Client;

class TelegramClient {
  private static $telegram_api = "api.telegram.org";
  private $client;

  public function __construct() {
    if(empty(Yii::$app->params['telegram_bot_token'])) {
      throw new Exception("telegram bot token not set as app params");
    }
    $this->client = new Client([
      'baseUrl' => sprintf("https://%s/bot%s", self::$telegram_api, Yii::$app->params['telegram_bot_token']),
    ]);
  }

  public function setWebhook($domain):TelegramResult {
    $webhook = 'https://' . $domain . '/telegram/webhook';
    $res = $this->client->get('setWebhook?url=' . $webhook)->send();
    return new TelegramResult($res);
  }

  public function getWebhookInfo():TelegramResult {
    $res = $this->client->get('getWebhookInfo')->send();
    return new TelegramResult($res);
  }

  public function sendMessage($chat_id, $message, $extra = []) {

  }

}
