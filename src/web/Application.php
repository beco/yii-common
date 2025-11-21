<?php

namespace beco\yii\web;

use yii\web\Application as BaseApplication;

class Application extends BaseApplication {

    private function isSecureConnection($request) {
      if($request->isSecureConnection) {
        return true;
      }
      if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
      }
      return false;
    }

    public function handleRequest($request) {
      if(!$this->isSecureConnection($request) && !in_array(getenv('environment'), ['local', 'dev'])) {
      $secureUrl = str_replace('http:', 'https:', $request->absoluteUrl);
      return Yii::$app->getResponse()->redirect($secureUrl, 301);
    } else {
      return parent::handleRequest($request);
    }
  }
}
