<?php

namespace effectivecore {
          use \effectivecore\console_factory as console;
          abstract class events_module_factory extends events_factory {

  static function on_init() {
    require_once('gl--file.php');
    require_once('gl--factory.php');
    require_once('gl--factory--cache.php');
    require_once('gl--factory--files.php');
    spl_autoload_register('\effectivecore\factory::autoload');
    settings_factory::init();
    translate_factory::init();
    token_factory::init();
    urls_factory::init();
    events_factory::init();
    entity_factory::init();
    core_factory::init();
  # init modules
    ob_start();
    console::set_log('init_core', '\effectivecore\events_module::on_init', 'Init calls');
    foreach (static::$data->on_init as $c_id => $c_event) {
      console::set_log($c_id, $c_event->handler, 'Init calls');
      call_user_func($c_event->handler);
    }
  }

}}