<?php

namespace beco\yii\models;

use Yii;
use beco\yii\db\ActiveRecord;
use yii\web\IdentityInterface;

abstract class User extends ActiveRecord implements IdentityInterface {

  const SCENARIO_SIGNUP = 'signup';
  const SCENARIO_EDIT = 'edit';
  const SCENARIO_LOGIN = 'login';
  const SCENARIO_DEFAULT = 'default';

  public string $password_open = "";
  public string $password_open_repeat = "";
  public string $otp_open;

  public static function tableName() {
    return '{{%users}}';
  }

  public function getId() {
    return $this->user_id;
  }

  public function rules() {
    return [
      ['email', 'unique'],
      ['email', 'email'],
      ['password', 'safe'],
      [['name', 'email'], 'required'],
      ['phone_number', 'string'],
      ['password', 'required', 'on' => self::SCENARIO_LOGIN],
      ['is_admin', 'boolean'],
      ['is_admin', 'default', 'value' => 0],
      ['blocked', 'boolean'],
      ['blocked', 'default', 'value' => 0],
      ['password_open', 'safe'],
      ['password_open', 'string', 'min' => 6],
      ['last_login', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
      ['password_open_repeat', 'compare', 'compareAttribute' => 'password_open', 'on' => self::SCENARIO_SIGNUP],
    ];
  }

  public function attributeLabels() {
    return [
      'email' => 'correo electrónico',
      'name' => 'nombre',
      'password' => 'password',
      'blocked' => 'acceso bloqueado',
      'password_open' => 'password',
      'password_open_repeat' => 'repetir password',
      'phone' => 'teléfono',
      'email_verification_token' => 'código de verificación',
      'email_verification_token_expiration' => 'fecha de expiración',
      'email_verified' => 'correo electrónico verificado',
    ];
  }

  public function beforeSave($insert) {
    if(!empty($this->password_open)) {
      $this->password = $this->ofuscatePassword($this->password_open);
    }
    return parent::beforeSave($insert);
  }

  public function ofuscatePassword($password) {
    return Yii::$app->security->generatePasswordHash($password);
  }

  public function checkPassword($candidate) {
    return $this->password === $this->ofuscatePassword($candidate);
  }

  public function canLogin():bool {
    return !empty($this->password) && $this->blocked == 0;
  }

  public function getIsAdmin() {
    return $this->is_admin == 1;
  }

  public function hasPassword() {
    return !empty($this->password);
  }

  public static function findIdentity($id) {
    return static::findOne($id);
  }

  public static function findIdentityByAccessToken($token, $type = null) {
    return static::findOne(['access_token' => $token]);
  }

  public function getAuthKey() {
    return $this->authKey;
  }

  public function validateAuthKey($authKey) {
    return $this->authKey === $authKey;
  }

  public function toString() {
    return sprintf('[%d] %s (%s)', $this->id, $this->name ?? 'sin nombre', $this->email);
  }
}
