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
  # init classes
    $handlers = [
      '\effectivecore\settings_factory::init',
      '\effectivecore\translate_factory::init',
      '\effectivecore\token_factory::init',
      '\effectivecore\urls_factory::init',
      '\effectivecore\events_factory::init',
      '\effectivecore\entity_factory::init',
      '\effectivecore\core_factory::init'
    ];
    foreach ($handlers as $c_handler) {
      timer::tap($c_handler);
      call_user_func($c_handler);
      timer::tap($c_handler);
      console::set_log(
        timer::get_period($c_handler, 0, 1).' sec.', $c_handler, 'Init calls'
      );
    }
  # on_init modules
    foreach (static::$data->on_init as $c_info) {
      $c_handler = $c_info->handler;
      timer::tap($c_handler);
      call_user_func($c_handler);
      timer::tap($c_handler);
      console::set_log(
        timer::get_period($c_handler, 0, 1).' sec.', $c_handler, 'Init calls'
      );
    }
  }

}}