<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          abstract class events_factory {

  static $data;

  static function init() {
    foreach (settings::$data['events'] as $c_module_events) {
      foreach ($c_module_events as $c_type => $c_events) {
        foreach ($c_events as $c_id => $c_event) static::$data->{$c_type}[$c_id] = $c_event;
        factory::array_sort_by_weight(static::$data->{$c_type});
      }
    }
  }

}}