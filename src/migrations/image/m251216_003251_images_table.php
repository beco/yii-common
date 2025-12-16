<?php

use yii\db\Migration;

class m251216_003251_images_table extends Migration {
  /**
   * {@inheritdoc}
   */
  public function safeUp() {
    $this->createTable('{{%images}}', [
      'image_id' => $this->primaryKey(),
      'uid' => $this->string(100)->notNull(),
      'model' => $this->string(100)->notNull(),
      'model_id' => $this->integer()->notNull(),
      'attribute' => $this->string()->notNull(),
      'metadata' => $this->json(),
      'path' => $this->string(200),
      'url' => $this->string(500),
      'created_at' => $this->timestamp()->notNull(),
      'updated_at' => $this->timestamp()->notNull()
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown() {
    $this->dropTable('{{%images}}');
  }
}
