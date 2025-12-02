<?php

namespace beco\yii\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use beco\yii\jobs\beat\BeatJob;

class BeatController extends Controller {

  public $defaultAction = 'info';

  public function actionStart() {
    //TODO: check if there is a queued BeatJob
    Yii::$app->queue->push(new BeatJob());
    $this->stdout("Beat has just been started\n");
    return ExitCode::OK;
  }

  public function actionRun($continue_queue = 1) {
    $job = new BeatJob(['continue_queue' => $continue_queue]);
    $job->execute(null);
    return ExitCode::OK;
  }

  public function actionInfo() {
    $sql = "SELECT * FROM queue";
    $rows = Yii::$app->db->createCommand($sql)->queryAll();
    foreach($rows as $row) {
      $job = unserialize($row['job']);
      echo $job::class . "\n";
      $d = date('Y-m-d H:i:s', $row['pushed_at']+ $row['delay']);
      printf("%s - %s - %s\n", $row['id'], $d, $row['channel']);
      if(method_exists($job, 'toString')) {
        printf("%s\n", $job->toString());
      }
      printf("--------------------------------\n");
    }
    return ExitCode::OK;
  }
}
