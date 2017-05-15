<?php

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          abstract class events_module_factory extends events_factory {

  static function on_init() {
    require_once('gl--file.php');
    require_once('gl--factory.php');
    require_once('gl--factory--cache.php');
    require_once('gl--factory--files.php');
    spl_autoload_register('\effectivecore\factory::autoload');
    timer::tap('total');
    timer::tap('init_core');
    settings_factory::init();
    translate_factory::init();
    token_factory::init();
    urls_factory::init();
    events_factory::init();
    entity_factory::init();
    core_factory::init();
    timer::tap('init_core');
    console::set_log(
      timer::get_period('init_core', 0, 1).' sec.', 'init_core | \effectivecore\events_module::on_init', 'Init calls'
    );
  # init modules
    ob_start();
    foreach (static::$data->on_init as $c_id => $c_event) {
      timer::tap('init_'.$c_id);
      call_user_func($c_event->handler);
      timer::tap('init_'.$c_id);
      console::set_log(
        timer::get_period('init_'.$c_id, 0, 1).' sec.', $c_id.' | '.$c_event->handler, 'Init calls'
      );
    }
  }

}}