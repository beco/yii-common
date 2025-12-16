<?php

namespace beco\yii\models;

use Yii;
use beco\yii\jobs\images\ImageUploaderJob;
use beco\yii\db\ActiveRecord;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * This class provides any ActiveRecord Model a way to extend it's attributes
 * which store an image, providing a way to centrally store them, upload it to
 * S3
 */
class Image extends ActiveRecord {

  public static function tableName() {
    return '{{%images}}';
  }

  public function getId() {
    return $this->image_id;
  }

  public function afterSave($insert, $attrs) {
    if($insert) {
      Yii::$app->queue->push(new ImageUploaderJob(['image_id' => $this->id]));
    }
    return parent::afterSave($insert, $attrs);
  }

  public function rules() {
    return [
      [['model', 'model_id', 'attribute'], 'unique', 'targetAttribute' => ['model', 'model_id', 'attribute']],
      [['model', 'model_id', 'attribute'], 'required'],
    ];
  }
}
