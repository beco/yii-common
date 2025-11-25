<?php

namespace beco\yii\modules\clients\telegram;

final class TelegramResult {
  public $isOk = false;
  public $errors = [];
  public $content;

  public function __construct($response) {
    $content = json_decode($response->getContent(), true);
    $this->isOk = $response->isOk && $content['ok'];
    $this->content = $content;
  }
}
