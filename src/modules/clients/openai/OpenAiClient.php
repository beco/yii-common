<?php

namespace beco\yii\modules\clients\openai;

use Yii;
use Exception;
use yii\httpclient\Client;

class OpenAiClient {

  private $api_key;
  private $client;
  private $response;

  public function __construct() {
    if(empty(Yii::$app->params['openai_api_key'])) {
      throw new Exception("No OpenAI API Key as a param");
    }

    $this->api_key = Yii::$app->params['openai_api_key'];

    $this->client = new Client([
      'baseUrl' => 'https://api.openai.com/v1',
    ]);

  }

  public function executePromptId($prompt_id, $input, $version = null) {
    $data = [
      'model' => 'gpt-4.1',
      'prompt' => [
        'id' => $prompt_id,
      ],
      'input' => [$input]
    ];
    if(!empty($version) && is_int($version)) {
      $data['prompt']['version'] = $version;
    }

    $res = $this->client->createRequest()
      ->addHeaders([
        'Authorization' => 'Bearer ' . $this->api_key,
        'Content-Type' => 'application/json',
      ])
      ->setMethod('POST')
      ->setUrl('responses')
      ->setData($data)
      ->send();
    if($res->isOk) {
      $this->response = $res->data;
      return $res->data;
    } else {
      throw new \Exception("error prompting: " . json_encode($res->content));
    }
  }

  public function getResponseData() {
    return $this->response['output'][0]['content'][0]['text'];
  }
}
