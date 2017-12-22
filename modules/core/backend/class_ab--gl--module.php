<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class module {

  static protected $data;

  static function init() {
    static::$data = storage::get('files')->select_group('module');
  }

  static function get_all() {
    if   (!static::$data) static::init();
    return static::$data;
  }

}}