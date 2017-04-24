<?php

namespace effectivecore {
          abstract class events {

  static $data;

  public function init() {
    foreach (settings_factory::$data['events'] as $c_module_events) {
      foreach ($c_module_events as $c_type => $c_events) {
        foreach ($c_events as $c_id => $c_event) static::$data->{$c_type}[$c_id] = $c_event;
        factory::array_sort_by_weight(static::$data->{$c_type});
      }
    }
  }

}}