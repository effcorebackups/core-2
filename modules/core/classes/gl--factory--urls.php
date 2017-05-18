<?php

namespace effectivecore {
          abstract class urls_factory {

  static $current;

  static function init() {
    static::$current = new url($_SERVER['REQUEST_URI']);
  }

  static function is_local($url) {
    return (new url($url))->domain == $_SERVER['HTTP_HOST'];
  }

  static function is_active($url) {
    return static::$current->get_full() == (new url($url))->get_full();
  }

  static function make_back_part() {
    return 'back='.urlencode(static::$current->get_full());
  }

  static function get_back_url() {
    $back_url = static::$current->get_args('back', 'query');
    return $back_url ? urldecode($back_url) : '';
  }

  static function go($url) {
    factory::send_header_and_exit('redirect', '',
      (new url($url))->get_full()
    );
  }

}}