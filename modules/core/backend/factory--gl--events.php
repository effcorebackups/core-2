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
    foreach (storages::get('settings')->select_group('events') as $c_events_by_module) {
      foreach ($c_events_by_module as $c_type => $c_events_by_type) {
        foreach ($c_events_by_type as $c_event) {
          static::$data[$c_type][$c_event->for][] = $c_event;
        }
      }
    }
    foreach (static::$data as $c_type => $c_grp_events) {
      foreach ($c_grp_events as &$c_events) {
        factory::array_sort_by_weight($c_events);
      }
    }
  }

  static function get() {
    if (!static::$data) static::init();
    return static::$data;
  }

  static function start($type, $id = null, $args = []) {
    $return = [];
    if (!empty(static::get()[$type])) {
      foreach (static::get()[$type] as $c_for => $c_events) {
        foreach ($c_events as $c_event) {
          if ($id == $c_for || $id == null) {
            timers::tap('event call: '.$c_for);
            $return[$c_event->handler][] = $c_return = call_user_func_array($c_event->handler, $args);
            timers::tap('event call: '.$c_for);
            console::add_log(
              'event', 'call', ltrim($c_event->handler, '\\'), $c_return ? 'ok' : '-', timers::get_period('event call: '.$c_for, -1, -2)
            );
          }
        }
      }
    }
    return $return;
  }

}}