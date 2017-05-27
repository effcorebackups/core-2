<?php

namespace effectivecore {
          abstract class console_factory {

  static $data = [];
  static $information = [];

  static function add_log($group = 'System', $name, $values, $time = 0) {
    static::$data[] = [
      'group'  => $group,
      'name'   => $name,
      'values' => $values,
      'time'   => $time,
    ];
  }

  static function add_information($param, $value) {
    static::$information[$param] = $value;
  }

  static function get_all_logs()        {return static::$data;}
  static function get_all_information() {return static::$information;}

  static function render() {
    return (new markup('console', [], [
      new markup('h2', [], 'Console'),
      static::render_logs(),
      static::render_information()
    ]))->render();
  }

  static function render_logs() {
    $head = [['Time', 'Group', 'Name', 'Values']];
    $body = [];
    foreach (static::get_all_logs() as $c_log) {
      $body[] = new table_body_row(['class' => factory::to_css_class($c_log['group'])], [
        new table_body_row_cell(['class' => 'time'],   $c_log['time']),
        new table_body_row_cell(['class' => 'group'],  $c_log['group']),
        new table_body_row_cell(['class' => 'name'],   $c_log['name']),
        new table_body_row_cell(['class' => 'values'], $c_log['values'])
      ]);
    }
    return (
      new table(['class' => 'logs'], $body, $head)
    )->render();
  }

  static function render_information() {
    $info = new markup('dl', ['class' => 'information'], []);
    foreach (static::get_all_information() as $c_param => $c_value) {
      $info->add_child(new markup('dt', [], $c_param));
      $info->add_child(new markup('dd', [], $c_value));
    }
    return $info->render();
  }

}}