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
      ['password_open', 'required', 'on' => self::SCENARIO_SIGNUP],
      ['otp', 'string'],
      ['extra', 'safe'],
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
      'is_admin' => 'admin',
      'roles' => 'Roles',
    ];
  }

  public function attributeHints() {
    return [
      'is_admin' => '¡Cuidado!',
    ];
  }

  public function beforeSave($insert) {
    if(!empty($this->password_open)) {
      $this->password = Yii::$app->security->generatePasswordHash($this->password_open);
    }
    if(empty($this->auth_key)) {
      $this->auth_key = Yii::$app->security->generateRandomString();
    }
    return parent::beforeSave($insert);
  }

  public function validatePassword($candidate) {
    return Yii::$app->security->validatePassword($candidate, $this->password);
  }

  public function canLogin():bool {
    return !empty($this->password) && $this->blocked == 0;
  }

  public function getIsAdmin():bool {
    return $this->is_admin == 1;
  }

  public function getRoles() {
    if(is_array($this->roles)) {
      $roles = $this->roles;
      if($this->isAdmin) {
        array_unsifht('admin', $roles);
      }
      return $roles;
    }
    return [];
  }

  public function hasRole($role):bool {
    return in_array($role, $this->getRoles());
  }

  public function addRole($role):bool {
    if($this->hasRole($role)) {
      return true;
    }
    $roles = $this->getRoles();
    array_unsifht($role, $roles);
    $this->roles = $roles;
    return $this->save(false);
  }

  public function hasPassword():bool {
    return !empty($this->password);
  }

  public static function findIdentity($id) {
    return static::findOne($id);
  }

  public static function findIdentityByAccessToken($token, $type = null) {
    return static::findOne(['access_token' => $token]);
  }

  public function getAuthKey() {
    return $this->auth_key;
  }

  public function getUsername() {
    return $this->name;
  }

  public function validateAuthKey($authKey) {
    return $this->authKey === $authKey;
  }

  public function toString():string {
    return sprintf('[%d] %s (%s)', $this->id, $this->name ?? 'sin nombre', $this->email);
  }

  public static function findByEmail($email):self|null {
    return static::findOne(['email' => $email]);
  }
}
