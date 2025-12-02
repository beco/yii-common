<?php

namespace beco\yii\models;

use Yii;
use Exception;
use yii\base\Model;

/**
 * A prompt-based connector to different engines
 */
class Prompt extends Model {

  private $client;
  private $file_name;
  private $prompt;
  private $engine;

  private static $clients = [
    'openai' => \beco\yii\modules\clients\openai\OpenAiClient::class,
  ];

  public function __construct($file_name, $engine = 'openai', $model = 'gpt-41') {

    if(!file_exists(Yii::getAlias('@app/prompts') . "/" . $file_name . ".prompt")) {
      throw new Exception("No such prompt file");
    }

    $this->engine = $engine;
    $this->client = new self::$clients[$this->engine];
    $this->file_name = Yii::getAlias('@app/prompts') . "/" . $file_name;
  }

  public function preparePrompt($variables = []):string {

  }

  public function execute() {

  }

  public function toString() {
    return sprintf("Executing %s with %s\n", $this->file_name, $this->engine);
  }
}
