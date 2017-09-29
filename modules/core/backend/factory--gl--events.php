<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_factory {

  protected static $data;

  static function init() {
    console::add_log('event', 'init.', 'the event system was initialized', '-');
    foreach (storages::get('settings')->select('events') as $module_id => $c_grp_events) {
      foreach ($c_grp_events as $c_type => $c_events) {
        foreach ($c_events as $c_id => $c_event) {
          static::$data->{$c_type}[$c_id] = $c_event;
        }
      }
    }
    foreach (static::$data as &$c_type) {
      factory::array_sort_by_weight($c_type);
    }
  }

  static function get() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function start($type, $id = null, $args = []) {
    $return = [];
    if (!empty(static::get()->{$type})) {
      foreach (static::get()->{$type} as $c_id => $c_info) {
        if ($id == null || $id == $c_id) {
          timers::tap('event call: '.$c_id);
          $return[$c_info->handler][] = $c_return = call_user_func_array($c_info->handler, $args);
          timers::tap('event call: '.$c_id);
          console::add_log(
            'event', 'call', ltrim($c_info->handler, '\\'), $c_return ? 'ok' : '-', timers::get_period('event call: '.$c_id, -1, -2)
          );
        }
      }
    }
    return $return;
  }

}}