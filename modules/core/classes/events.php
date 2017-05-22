<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          abstract class events {

  protected static $data;

  static function init() {
    foreach (settings::get('events') as $c_module_events) {
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