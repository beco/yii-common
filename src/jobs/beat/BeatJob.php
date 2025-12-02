<?php

namespace beco\yii\jobs\beat;

use Yii;
use Exception;
use yii\queue\JobInterface;
use beco\yii\interfaces\BeatInterface;

class BeatJob implements JobInterface {
  public $beat_lapse = 60;
  public $continue_queue = 1;

  public function __construct($params = []) {
    $this->continue_queue = $params['continue_queue'] ?? 1;
  }

  public function execute($queue) {
    if(empty(Yii::$app->queue)) {
      throw new Exception("No queue in app");
    }

    $models = Yii::$app->params['beatModels'] ?? [];

    try {
      foreach($models as $model) {
        $model = new $model;
        if(! $model instanceof BeatInterface) { //<-- here...
          continue;
        }
        $model::executeBeat();
      }
    } catch(Exception $e) {
      Yii::error(['msg' => 'Error in queue', 'extra' => $e->getMessage()]);
    } finally {
      if($this->continue_queue) {
        Yii::$app->queue->delay($this->beat_lapse)->push(new BeatJob());
      }
    }
  }
}

/*
Here I want to read recursivelly the Model's folder and grab all Models which
implement the BeatInterface
*/
