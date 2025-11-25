<?php

namespace beco\yii\models;

use Yii;
use beco\yii\db\ActiveRecord;


class ModelLog extends ActiveRecord {

  public static function tableName() {
    return '{{%model_log}}';
  }

  public function getId() {
    return $this->model_log_id;
  }

  public function rules() {
    return [
      [['user_id', 'model_id'], 'integer'],
      [['model_class', 'action'], 'string'],
      [['model_class', 'model_id'], 'required'],
      [['old_values', 'new_values'], 'safe'],
    ];
  }

  public function beforeSave($insert) {
    if(empty($this->created_at)) {
      $this->created_at = date('Y-m-d H:i:s');
    }
    return parent::beforeSave($insert);
  }
}
