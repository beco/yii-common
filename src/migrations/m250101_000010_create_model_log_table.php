<?php

namespace beco\yii\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%model_log}}`.
 */
class m250101_000010_create_model_log_table extends Migration {

  /**
   * {@inheritdoc}
   */
  public function safeUp() {
    $this->createTable('{{%model_log}}', [
      'model_log_id' => $this->primaryKey(),
      'user_id' => $this->integer(),
      'model_class' => $this->string(100)->notNull(),
      'model_id' => $this->integer()->notNull(),
      'action' => $this->string(20),
      'old_values' => $this->json(),
      'new_values' => $this->json(),
      'created_at' => $this->timestamp()->notNull(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown() {
      $this->dropTable('{{%model_log}}');
  }
}
