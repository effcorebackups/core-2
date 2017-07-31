<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class events {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select('events') as $module_id => $c_events) {
      foreach ($c_events as $c_type => $c_events) {
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
    if (!empty(events::get()->{$type})) {
      foreach (events::get()->{$type} as $c_id => $c_info) {
        if ($id == null || $id == $c_id) {
          call_user_func_array($c_info->handler, $args);
        }
      }
    }
  }

}}