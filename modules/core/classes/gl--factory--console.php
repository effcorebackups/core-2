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
    return (new markup('x-console', [], [
      new markup('h2', [], 'Execute plan'), static::render_logs(),
      new markup('h2', [], 'Total load'),   static::render_diagram_load(),
      new markup('h2', [], 'Information'),  static::render_information()
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

  static function render_diagram_load() {
    $statistics = [];
    $total = 0;
    foreach (static::$data as $c_log) {
      if(floatval($c_log['time'])) {
        if (!isset($statistics[$c_log['group']])) $statistics[$c_log['group']] = 0;
        $statistics[$c_log['group']] += floatval($c_log['time']);
        $total += floatval($c_log['time']);
      }
    }
    $diagram = new markup('dl', ['class' => 'diagram-load'], []);
    foreach ($statistics as $c_param => $c_value) {
      $diagram->child_insert(new markup('dt', [], $c_param));
      $diagram->child_insert(new markup('dd', [], [
        number_format($c_value, 6).' sec. ('.
        number_format($c_value / $total * 100, 1).'%)',
        new markup('div', [
          'class' => 'scale scale-'.factory::to_css_class($c_param),
          'style' => 'width:'.(int)($c_value / $total * 100).'%'
        ], ' ')
      ]));
    }
    return $diagram->render();
  }

  static function render_information() {
    $info = new markup('dl', ['class' => 'information'], []);
    foreach (static::get_all_information() as $c_param => $c_value) {
      $info->child_insert(new markup('dt', [], $c_param));
      $info->child_insert(new markup('dd', [], $c_value));
    }
    return $info->render();
  }

}}