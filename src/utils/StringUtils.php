<?php

namespace beco\yii\utils;

class StringUtils {

  private static $numbers = "0123456789";
  private static $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#_-?";
  private static $simple_chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
  private static $special_url_chars = ['Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'];

  public static function generate($length = 10, $source = 'full') {
    $set = self::$chars;
    if($source == 'numbers') {
      $set = self::$numbers;
    } elseif($source == 'simple') {
      $set = self::$simple_chars;
    }
    $chars_len = strlen($set);
    $random_string = '';
    for($i = 0; $i < $length; $i++) {
      $random_string .= $set[random_int(0, $chars_len - 1)];
    }
    return $random_string;
  }

  public static function slugify($string, $space = '-') {
    $slug = strtr($string, self::$special_url_chars);
    $slug = strtolower($slug);
    $slug = preg_replace('/\s+/', $space, $slug);
    $slug = preg_replace('/[^a-z0-9]/', '-', $slug);
    $slug = preg_replace('/\-+/', '-', $slug);
    $slug = preg_replace('/\-$/', '', $slug);
    $slug = preg_replace('/^\-/', '', $slug);
    return $slug;
  }

  public static function mdToHtml($md) {
    if(empty($md)) {
      return "";
    }
    $html = preg_replace('/\_(.*?)\_/', '<i>$1</i>', $md);
    $html = preg_replace('/\*(.*?)\*/', '<b>$1</b>', $html);
    $html = preg_replace('/\~(.*?)\~/', '<s>$1</s>', $html);
    $html = preg_replace('/^### (.+)\n/', '<h3>$1</h3>', $html);
    $html = preg_replace('/\n(\s*\n)+/', '</p><p>', $html);
    $html = preg_replace('/\n/', '<br>', $html);
    $html = '<p>'.$html.'</p>';
    return $html;
  }

  public static function clean($string) {

  }

  public static function initials($string) {
    $parts = explode(" ", trim($string));
    $initials = "";
    foreach($parts as $part) {
      if(!empty($part)) {
        $initials .= strtoupper($part[0]);
      }
    }
    return $initials;
  }

  public static function minimize($string) {
    $string = self::slugify($string, '_');
    $string = preg_replace('/[aeiou]/', '', $string);
    return strtolower(trim($string));
  }

  public static function highlight($string, $search) {
    return str_ireplace($search, sprintf('<mark>%s</mark>', $search), $string);
  }

  /**
   * Get a code from a positive integer
   * @param int $i
   * @param int a prime number to get a unique result
   * @return string
   * @todo Move to StringUtils class
   */
  public static function getCodeFromInt($i, $seed = 17, $prefix = '') {
    if(!is_int($i) or $i < 0) {
      throw new \Exception('Invalid number, must be a positive integer');
    }
    $base = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $base_len = strlen($base);
    $code = '';
    $i = $i * $seed;
    while($i > 0) {
      $code = $base[$i % $base_len] . $code;
      $i = floor($i / $base_len);
    }
    return $prefix . $code;
  }
}
