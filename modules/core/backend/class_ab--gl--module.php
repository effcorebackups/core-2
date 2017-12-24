<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class module {

  static protected $cache;

  static function init() {
    static::$cache = storage::get('files')->select_group('module');
  }

  static function get_all() {
    if   (!static::$cache) static::init();
    return static::$cache;
  }

}}