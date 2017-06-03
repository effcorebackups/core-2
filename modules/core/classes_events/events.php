<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class events {

  protected static $data;

  static function init() {
    foreach (storages::get('settings')->select('events') as $c_module_events) {
      foreach ($c_module_events as $c_type => $c_events) {
        foreach ($c_events as $c_id => $c_event) static::$data->{$c_type}[$c_id] = $c_event;
        factory::array_sort_by_weight(static::$data->{$c_type});
      }
    }
  }

  static function get() {
    if (!static::$data) static::init();
    return static::$data;
  }

}}