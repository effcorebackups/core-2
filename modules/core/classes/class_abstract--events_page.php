<?php

namespace effectivecore {
          abstract class events_page extends events {

  static function on_show_install() {
  # if (version_compare(phpversion(), '5.6.0') < 0) print "PHP is too old!\nCurrent version: ".phpversion()."\nRequired version: 5.6.0+";
    $call_stack = factory::collect_by_property(settings::$data['module'], 'on_install');
    foreach (factory::array_sort_by_weight($call_stack) as $c_event) {
      call_user_func($c_event->handler);
    }
  }

}}