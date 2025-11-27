<?php

namespace beco\yii\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */

class m250101_000000_create_users_table extends Migration {
  /**
   * {@inheritdoc}
   */
  public function safeUp() {
    $this->createTable('{{%users}}', [
      'user_id' => $this->primaryKey()->unsigned(),
      'uid' => $this->string()->notNull()->unique(),
      'name' => $this->string(50),
      'email' => $this->string(200),
      'phone_number' => $this->string(15)->unique(),
      'password' => $this->string(500),
      'blocked' => $this->tinyInteger()->notNull()->defaultValue('0'),
      'auth_key' => $this->string(200),
      'access_token' => $this->string(200),
      'is_admin' => $this->tinyInteger()->notNull()->defaultValue('0'),
      'otp' => $this->string(),
      'otp_expiration' => $this->integer(),
      'chat_id' => $this->integer(), //telegram
      'extra' => $this->json(), // Full json object for each project's purposes
      'roles' => $this->json(), // Meant to be a json array ['client', 'operator', ...]
      'last_login' => $this->timestamp(),
      'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
      'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('on update current_timestamp'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown() {
    $this->dropTable('{{%users}}');
  }
}
