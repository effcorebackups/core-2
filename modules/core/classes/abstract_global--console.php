<?php

namespace effectivecore {
          abstract class console {

  static $data = [];

  static function set_log($key, $value, $group = 'Total') {
    static::$data[$group][] = (object)['key' => $key, 'value' => $value];
  }

  static function get_all_logs() {
    return static::$data;
  }

  static function render() {
    $sections = [];
    foreach (static::get_all_logs() as $c_section_title => $c_section) {
      $c_data = [];
      foreach ($c_section as $c_log) {
        $c_data[] = new html('dt', [], $c_log->key);
        $c_data[] = new html('dd', [], $c_log->value);
      }
      $sections[] = new html('section', ['class' => factory::to_css_class($c_section_title)], [
        new html('h2', [], $c_section_title),
        new html('dl', [], $c_data),
      ]);
    }
    return (
      new html('console', [], $sections)
    )->render();
  }

}}