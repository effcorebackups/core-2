<?php

namespace effectivecore {
          abstract class urls {

  static $current;

  static function init() {
    static::$current = new url($_SERVER['REQUEST_URI']);
  }

  static function is_local($url) {
    return (new url($url))->domain == $_SERVER['HTTP_HOST'];
  }

  static function is_active($url) {
    return static::$current->full() == (new url($url))->full();
  }

  static function go($url) {
    factory::send_header_and_exit('redirect', '',
      (new url($url))->full()
    );
  }

}}