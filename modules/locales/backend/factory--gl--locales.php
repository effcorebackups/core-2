<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class locales_factory {

  protected static $current;

  static function init() {
    static::$current = storages::get('settings')->select('current')['locales'];
  }

  static function get_current() {
    if (!static::$current) static::init();
    return static::$current;
  }

}}