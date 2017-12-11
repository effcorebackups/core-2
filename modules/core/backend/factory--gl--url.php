<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          abstract class url_factory {

  protected static $data;

  static function init() {
    static::$data = new url($_SERVER['REQUEST_URI']);
  }

  static function select_current() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function select_back_url() {
    $back_url = static::select_current()->get_args('back', 'query');
    return $back_url ? urldecode($back_url) : '';
  }

  static function make_back_part() {
    return 'back='.urlencode(static::select_current()->get_full());
  }

  static function is_local($url) {
    return (new url($url))->domain == $_SERVER['HTTP_HOST'];
  }

  static function is_active($url) {
    return static::select_current()->get_full() == (new url($url))->get_full();
  }

  static function go($url) {
    factory::send_header_and_exit('redirect', '',
      (new url($url))->get_full()
    );
  }

}}