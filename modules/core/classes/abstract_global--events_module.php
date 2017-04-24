<?php

namespace effectivecore {
          abstract class events_module extends events {

  static function on_init() {
    require_once('gl--factory--cache.php');
    require_once('abstract_global--factory.php');
    require_once('abstract_global--files.php');
    require_once('global--file.php');
    spl_autoload_register('\effectivecore\factory::autoload');
    settings::init();
    translate_factory::init();
    token_factory::init();
    urls_factory::init();
    events::init();
    core_factory::init();
  # init modules
    ob_start();
    console_factory::set_log('init_core', '\effectivecore\events_module::on_init', 'Init calls');
    foreach (static::$data->on_init as $c_id => $c_event) {
      console_factory::set_log($c_id, $c_event->handler, 'Init calls');
      call_user_func($c_event->handler);
    }
  }

}}