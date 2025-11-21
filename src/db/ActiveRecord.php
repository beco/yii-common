<?php

namespace beco\yii\db;

use DateTime;
use DateTimeInterface;
use yii\helpers\Inflector;
use yii\db\ActiveRecord as YiiActiveRecord;
use beco\yii\db\exceptions\MultipleRecordsFoundWhenOnlyOneIsExpceted;
use beco\yii\db\exceptions\ModelNotSaved;
use beco\yii\utils\DateUtils;

abstract class ActiveRecord extends YiiActiveRecord {

  private array $_virtualCache = [];

  public static function getOrCreate(array $conditions, array $attributes = [], $force = false):self|null {
    $model = static::find()->where($conditions)->all();

    if(is_array($model) && count($model) > 1) {
      throw new MultipleRecordsFoundWhenOnlyOneIsExpceted();
    }

    $model = $model[0] ?? null;

    if(empty($model)) {
      $model = new static;
      $model->setAttributes($conditions);
      $model->setAttributes($attributes);
      if(!$model->save(!$force)) {
        throw new ModelNotSaved(sprintf("Cannot create %s, errors: %s",
          static::class,
          json_encode($model->errors)
        ));
      }
    }

    return $model;
  }

  public static function createOrUpdate(array $conditions, array $attributes) {
    $model = self::getOrCreate($conditions);
    $model->setAttributes($attributes);
    if(!$model->save()) {
      throw new ModelNotSaved(sprintf("Cannot save %s, errors: %s",
        static::class,
        json_encode($model->errors)
      ));
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

    if($this->hasAttribute('uid') && empty($this->uid)) {
      $this->uid = uniqid();
    }

    return true;
  }

  /**
   * Maneja métodos dinámicos tipo:
   * - getStartsAtRelative()
   * - getStartsAtHuman()
   */
  public function __call($name, $params) {
    if (preg_match('/^get(.+?)(Relative|Human|DateTime)$/', $name, $matches)) {
      $baseCamel = $matches[1];   // e.g. "StartsAt"
      $suffix    = $matches[2];   // "Relative" | "Human"

      $baseProp  = lcfirst($baseCamel);            // "startsAt"
      $cacheKey  = $baseProp . $suffix;            // "startsAtRelative"

      // 2) Intentar usar un getter explícito: getStartsAt()
      $getter = 'get' . $baseCamel;
      if (method_exists($this, $getter)) {
        $value = $this->$getter(...$params);
        echo "value: {$value}\n";
      } else {
        // 3) Si no hay getter, intentar ir directo al atributo BD: starts_at


        $snake = Inflector::camel2id($baseProp, '_'); // "starts_at"
        echo "snake: {$snake}\n";

        if ($this->hasAttribute($snake)) {
          $raw = $this->getAttribute($snake);

          if ($raw instanceof \DateTimeInterface) {
            $value = $raw;
          } elseif ($raw === null || $raw === '') {
            $value = null;
          } else {
            // Aquí puedes poner tu lógica de parseo de fecha
            $value = new \DateTimeImmutable($raw);
          }
        } else {
          // No sabemos qué hacer, dejamos que el padre maneje el error
          return parent::__call($name, $params);
        }
      }

      if($value instanceof DateTimeInterface) {
        $value = $value;
      } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
        $value = new DateTime($value);
      } elseif(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        $value = new DateTime($value);
      } else {
        return parent::__call($name, $params);
      }

      $result = match ($suffix) {
        'Relative' => DateUtils::getRelativeTime($value),
        'Human'    => DateUtils::getHumanDate($value),
        'DateTime' => $value,
        default    => null,
      };

      // 5) Cache
      $this->_virtualCache[$cacheKey] = $result;

      return $result;
    }

    return parent::__call($name, $params);
  }

  /**
   * Opcional: permitir $model->startsAtRelative y $model->startsAtHuman
   * además de $model->getStartsAtRelative()
   */
  public function __get($name) {
    if (preg_match('/^(.+?)(Relative|Human)$/', $name, $matches)) {
      $baseProp = $matches[1];      // "startsAt"
      $suffix   = $matches[2];      // "Relative" | "Human"

      $method = 'get' . ucfirst($baseProp) . $suffix; // getStartsAtRelative

      // Aunque __call maneja el método, simplemente lo invocamos
      return $this->$method();
    }

    return parent::__get($name);
  }

}
