<?php

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          abstract class events_module_factory extends events_factory {

  static function on_init() {
    timer::tap('total');
  # init classes
    $handlers = [
      '\effectivecore\entity_factory::init',
      '\effectivecore\core_factory::init'
    ];
    foreach ($handlers as $c_handler) {
      timer::tap($c_handler);
      call_user_func($c_handler);
      timer::tap($c_handler);
      console::add_log(
        'Call', $c_handler, '-', timer::get_period($c_handler, 0, 1)
      );
    }
  # on_init modules
    foreach (static::get()->on_init as $c_info) {
      $c_handler = $c_info->handler;
      timer::tap($c_handler);
      call_user_func($c_handler);
      timer::tap($c_handler);
      console::add_log(
        'Call', $c_handler, '-', timer::get_period($c_handler, 0, 1)
      );
    }
  }

}}