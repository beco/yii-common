<?php

namespace beco\yii\utils;

use Yii;
use DateTime;
use DateTimeZone;
use DateInterval;
use DateTimeInterface;

class DateUtils {

  private static $months_translations = [
    'january' => 'enero',
    'february' => 'febrero',
    'march' => 'marzo',
    'april' => 'abril',
    'may' => 'mayo',
    'june' => 'junio',
    'july' => 'julio',
    'august' => 'agosto',
    'september' => 'septiembre',
    'october' => 'octubre',
    'november' => 'noviembre',
    'december' => 'diciembre',
  ];

  private static $days_translations = [
    'monday' => 'lunes',
    'tuesday' => 'martes',
    'wednesday' => 'miércoles',
    'thursday' => 'jueves',
    'friday' => 'viernes',
    'saturday' => 'sábado',
    'sunday' => 'domingo',
  ];

  public static function getHumanDate(DateTimeInterface $date, $format = "eeee d 'de' MMMM 'de' yyyy 'a las' h:mm a") {
    $f = Yii::$app->formatter->asDatetime($date, $format);
    $f = preg_replace_callback('/\b(' . implode('|', array_keys(self::$days_translations)) . ')\b/i', function($matches) {
      return self::$days_translations[strtolower($matches[1])];
    }, $f);
    $f = preg_replace_callback('/\b(' . implode('|', array_keys(self::$months_translations)) . ')\b/i', function($matches) {
      return self::$months_translations[strtolower($matches[1])];
    }, $f);
    return $f;
  }

  public static function getRelativeTime(DateTimeInterface $target, ?DateTimeInterface $reference = null, ?string $timezone = null): string {
    $tz = $target->getTimezone();

    $reference = $reference
      ? (clone $reference)->setTimezone($tz)
      : new DateTime('now', $tz);

    // Normalizar fechas para comparar únicamente el día
    $targetDate    = (clone $target)->setTime(0, 0, 0);
    $referenceDate = (clone $reference)->setTime(0, 0, 0);

    $diff = (int)$referenceDate->diff($targetDate)->format('%r%a'); // días con signo
    $diff_m = (int)$referenceDate->diff($targetDate)->format('%r%i');

    if($diff == 0 && abs($diff_m) < 30) {
      if($diff_m < 0) {
        return sprintf("hace unos momentos");
      } else {
        return sprintf("en unos momentos");
      }
    }

    // Parte del día
    $hour = (int)$target->format('H');
    $part = match(true) {
      $hour < 6  => 'en la madrugada',
      $hour < 12 => 'en la mañana',
      $hour < 14 => 'al mediodía',
      $hour < 19 => 'en la tarde',
      default    => 'en la noche',
    };

    // Función helper para agregar parte del día
    $withPart = fn(string $base) => "{$base} {$part}";
    $withPastPart = fn(string $base) => "hace {$base}";

    return match ($diff) {
      0  => $withPart('hoy'),
      1  => $withPart('mañana'),
      2  => $withPart('pasado mañana'),
      -1 => $withPart('ayer'),
      -2 => $withPart('anteayer'),
      default => match (true) {
        // futuros
        $diff > 2 && $diff < 7 => "en {$diff} días",
        $diff >= 7 => "en " . floor($diff / 7) . " semana" .   (floor($diff / 7) > 1 ? 's' : ''),

        // pasados
        $diff < -2 && $diff > -7 => $withPastPart(abs($diff) . " días"),
        $diff <= -7 => $withPastPart(abs(floor($diff / 7)) . " semana" .
          (abs(floor($diff / 7)) > 1 ? 's' : '')),
        default => "hace mucho tiempo"
      }
    };
  }

  public static function getRandomDate(?DateTime $from = null, ?DateTime $to = null) {
    if(empty($from)) {
      $from = new DateTimeImmutable();
    }

    if(!empty($to)) {
      $range = (int) $to->diff($from, true)->days;
    } else {
      $range = 1000;
    }

    echo "range: {$range}\n";

    $days = rand(1, $range);
    echo "days: {$days}\n";

    return $from->add(DateInterval::createFromDateString("+{$days} days"));
  }
}
