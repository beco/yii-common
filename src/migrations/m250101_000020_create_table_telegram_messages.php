<?php

namespace beco\yii\migrations;

use yii\db\Migration;

class m250101_000020_create_table_telegram_messages extends Migration {

  public function safeUp() {
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
      $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
    }

    $this->createTable(
      '{{%telegram_messages}}',
      [
        'message_id' => $this->primaryKey()->unsigned(),
        'status' => $this->string(20)->notNull(),
        'update_id' => $this->integer()->unsigned(),
        'chat_id' => $this->bigInteger()->unsigned(),
        'direction' => $this->string(15)->notNull(),
        'username' => $this->string(50),
        'timestamp' => $this->integer()->unsigned(),
        'text' => $this->string(4096),
        'media_group_id' => $this->bigInteger(),
        'file_id' => $this->string(),
        'file_status' => $this->string(20),
        'file_url' => $this->string(500),
        'raw' => $this->json(),
        'created_at' => $this->dateTime()->notNull(),
        'updated_at' => $this->dateTime()->notNull(),
      ],
      $tableOptions
    );
  }

  public function safeDown() {
    $this->dropTable('{{%telegram_messages}}');
  }
}
