<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class locale_factory {

  protected static $countries;
  protected static $settings;

  static function init() {
    static::$settings = storage::get('settings')->select_group('current')['locales'];
    foreach (storage::get('settings')->select_group('countries') as $c_countries) {
      foreach ($c_countries as $c_country) {
        static::$countries[$c_country->code] = $c_country;
      }
    }
  }

  static function get_countries() {
    if (!static::$countries) static::init();
    return static::$countries;
  }

  static function get_settings() {
    if (!static::$settings) static::init();
    return static::$settings;
  }

  ###############
  ### formats ###
  ###############

  static function format_time($time)                      {return \DateTime::createFromFormat('H:i:s',       $time,     new \DateTimeZone('UTC'))->setTimezone(new \DateTimeZone(static::get_settings()->timezone))->format(static::get_settings()->format_time);}
  static function format_date($date)                      {return \DateTime::createFromFormat('Y-m-d',       $date,     new \DateTimeZone('UTC'))->setTimezone(new \DateTimeZone(static::get_settings()->timezone))->format(static::get_settings()->format_date);}
  static function format_datetime($datetime)              {return \DateTime::createFromFormat('Y-m-d H:i:s', $datetime, new \DateTimeZone('UTC'))->setTimezone(new \DateTimeZone(static::get_settings()->timezone))->format(static::get_settings()->format_datetime);}
  static function format_persent($number, $precision = 2) {return static::format_number(floatval($number), $precision).'%';}
  static function format_msecond($number, $precision = 6) {return static::format_number(floatval($number), $precision);}
  static function format_version($number)                 {return static::format_number(floatval($number), 2);}

  static function format_number($number, $precision = 0, $dec_point = null, $thousands = null, $no_zeros = true) {
    $current = static::get_settings();
    $precision = $precision ? $precision + 5 : 0; # disable the rounding effect
    $dec_point = is_null($dec_point) ? $current->decimal_point       : $dec_point;
    $thousands = is_null($thousands) ? $current->thousands_separator : $thousands;
    $return = $precision ? substr(number_format($number, $precision, $dec_point, $thousands), 0, -5) :
                                  number_format($number, $precision, $dec_point, $thousands);
    if ($no_zeros) {
      $return = rtrim($return, '0');
      $return = rtrim($return, $dec_point);
    }
    return $return;
  }

  static function format_human_bytes($bytes, $decimals = 2) {
    $pow = $bytes == 0 ? 0 : (int)log($bytes, 1024);
    $character = ['B', 'K', 'M', 'G', 'T'][$pow];
    $value = $bytes >= 1024 ? $bytes / pow(1024, $pow) : $bytes;
    return static::format_number($value, $decimals).$character;
  }

}}