<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          use \effectivecore\modules\storage\storage as storage;
          abstract class event_factory {

  protected static $data;

  static function init() {
    console::add_log('event', 'init.', 'event system was initialized', '-');
    foreach (storage::get('settings')->select_group('events') as $c_events_by_module) {
      foreach ($c_events_by_module as $c_type => $c_events_by_type) {
        foreach ($c_events_by_type as $c_event) {
          static::$data[$c_type][] = $c_event;
        }
      }
    }
    foreach (static::$data as $c_type => &$c_events) {
      if (count($c_events) > 1) {
        factory::array_sort_by_weight($c_events);
      }
    }
  }

  static function select_all() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function start($type, $id = null, $args = []) {
    $return = [];
    if (!empty(static::select_all()[$type])) {
      foreach (static::select_all()[$type] as $c_event) {
        if ($id == $c_event->for || $id == null) {
          timer::tap('event call: '.$c_event->for);
          $return[$c_event->handler][] = $c_return = call_user_func_array($c_event->handler, $args);
          timer::tap('event call: '.$c_event->for);
          console::add_log(
            'event', 'call', ltrim($c_event->handler, '\\'), $c_return ? 'ok' : '-', timer::select_period('event call: '.$c_event->for, -1, -2)
          );
        }
      }
    }
    return $return;
  }

}}