<?php

namespace beco\yii\models;

use Yii;
use beco\yii\db\ActiveRecord;

class TelegramMessage extends ActiveRecord {

  public static $status = [
    'new' => [
      'label' => 'new',
    ],
    'processing' => [],
    'processed' => [],
    'error' => [
      'error'
    ]

  ];

  public static function tableName() {
    return '{{%telegram_messages}}';
  }

  public function getId() {
    return $this->id;
  }

  public static function createFromUpdate($update, $direction = 'inbound') {

    if(!is_array($update)) {
      $update = json_decode($update, true);
    }

    Yii::info(['msg' => 'data', 'data' => $update]);

    $message = new TelegramMessage;
    $message->message_id = $update['message']['message_id'];
    $message->update_id = $update['update_id'];
    $message->chat_id = $update['message']['chat']['id'];
    $message->direction = $direction;
    $message->username = $update['message']['chat']['username'];
    $message->timestamp = $update['message']['date'];
    $message->text = $update['message']['text'] ?? $update['message']['caption'] ?? '';
    $message->status = 'new';

    //$this->media_group_id =
    //$this->file_id =
    //$this->file_status =
    //$this->file_url =

    $message->save(false);
    return $message;
  }
}
