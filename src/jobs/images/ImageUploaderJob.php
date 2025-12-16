<?php

namespace beco\yii\jobs\images;

use Yii;
use Exception;
use yii\queue\JobInterface;
use beco\yii\models\Image;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ImageUploaderJob implements JobInterface {

  public $image_id;
  public $delete_local = false;

  public function __construct($params = []) {
    $this->image_id = $params['image_id'] ?? null;
    $this->delete_local = $params['delete_local'] ?? false;
  }

  public function execute($queue) {
    if(empty($this->image_id)) {
      return;
    }
    if(empty(Yii::$app->params['aws_s3_bucket']) || empty(Yii::$app->params['aws_key'])) {
      printf("No AWS/S3 conf in App params\n");
      return;
    }

    $s3 = new S3Client([
      'version' => 'latest',
      'region' => Yii::$app->params['aws_region'],
      'credentials' => [
        'key' => Yii::$app->params['aws_key'],
        'secret' => Yii::$app->params['aws_secret'],
      ],
    ]);

    $image = Image::findOne($this->image_id);

    $path = $image->path;

    $file_content = file_get_contents($path);
    $file_extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'file';
    $content_type = mime_content_type($path);
    $key = sprintf("public/images/%s.%s", $image->uid, $file_extension);

    try {
      $s3->putObject([
        'Bucket' => Yii::$app->params['aws_s3_bucket'],
        'Key' => $key,
        'ContentType' => $content_type,
        'IfNoneMatch' => '*',
        'Body' => $file_content,
        //'ACL' => 'public-read',
        'CacheControl' => 'public, max-age=31536000, immutable',
      ]);

      if($this->delete_local) {
        unlink($image->url);
      }

      $image->url = $s3->getObjectUrl(Yii::$app->params['aws_s3_bucket'], $key);
      $image->save();
    } catch(AwsException $e) {
      if ($e->getAwsErrorCode() !== 'PreconditionFailed') {
        throw $e;
      }
    }

  }
}
