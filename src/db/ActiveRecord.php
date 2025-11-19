<?php

namespace beco\yii\db;

use yii\db\ActiveRecord as YiiActiveRecord;
use beco\yii\db\exceptions\MultipleRecordsFoundWhenOnlyOneIsExpceted;
use beco\yii\db\exceptions\ModelNotSaved;

abstract class ActiveRecord extends YiiActiveRecord {

  public static function getOrCreate(array $conditions, array $attributes = []):self {
    $model = static::find()->where($conditions)->all();

    if(count($model) > 1) {
      throw new MultipleRecordsFoundWhenOnlyOneIsExpceted();
    }

    if(empty($model)) {
      $model = new static;
      $model->setAttributes($conditions);
      $model->setAttributes($attributes);
      if(!$model->save()) {
        throw new ModelNotSaved(sprintf("Cannot create %s, errors: %s",
          static::class,
          json_encode($model->errors)
        ));
      }
    }
    return $model;
  }

  public function beforeSave($insert) {
    if(!parent::beforeSave($insert)) {
      return false;
    }

    if($insert && $this->hasAttribute('created_at')) {
      $this->created_at = date('Y-m-d H:i:s');
    }

    if($this->hasAttribute('updated_at')) {
      $this->updated_at = date('Y-m-d H:i:s');
    }

    if($this->hasAttribute('uid')) {
      $this->uid = uniqid();
    }

    return true;
  }
}
