<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          abstract class console_factory {

  static $data = [];
  static $information = [];

  static function add_log($object, $action, $description = '', $values = '', $time = 0) {
    static::$data[] = [
      'object'      => $object,
      'action'      => $action,
      'description' => $description,
      'values'      => $values,
      'time'        => $time,
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
    $head = [['Time', 'Object', 'Action', 'Description', 'Value']];
    $body = [];
    foreach (static::get_all_logs() as $c_log) {
      $row_class = factory::to_css_class($c_log['object']);
      $body[] = new table_body_row(['class' => [$row_class => $row_class]], [
        new table_body_row_cell(['class' => ['time'        => 'time']],          $c_log['time']),
        new table_body_row_cell(['class' => ['object'      => 'object']],        $c_log['object']),
        new table_body_row_cell(['class' => ['action'      => 'action']],        $c_log['action']),
        new table_body_row_cell(['class' => ['description' => 'description']],   $c_log['description']),
        new table_body_row_cell(['class' => ['values'      => 'values']],        $c_log['values'])
      ]);
    }
    return (
      new table(['class' => ['logs' => 'logs']], $body, $head)
    )->render();
  }

  static function render_diagram_load() {
    $statistics = [];
    $total = 0;
    foreach (static::$data as $c_log) {
      if(floatval($c_log['time'])) {
        if (!isset($statistics[$c_log['object']]))
                   $statistics[$c_log['object']] = 0;
        $statistics[$c_log['object']] += floatval($c_log['time']);
        $total += floatval($c_log['time']);
      }
    }
    $diagram = new markup('dl', ['class' => ['diagram-load' => 'diagram-load']], []);
    foreach ($statistics as $c_param => $c_value) {
      $diagram->child_insert(new markup('dt', [], $c_param));
      $diagram->child_insert(new markup('dd', [], [
        number_format($c_value, 6).' sec. ('.
        number_format($c_value / $total * 100, 1).'%)',
        new markup('div', [
          'class' => ['scale' => 'scale', 'scale-x' => 'scale-'.factory::to_css_class($c_param)],
          'style' => ['width: '.(int)($c_value / $total * 100).'%']
        ], ' ')
      ]));
    }
    return $diagram->render();
  }

  static function render_information() {
    $info = new markup('dl', ['class' => ['info' => 'info']], []);
    foreach (static::get_all_information() as $c_param => $c_value) {
      $info->child_insert(new markup('dt', [], $c_param));
      $info->child_insert(new markup('dd', [], $c_value));
    }
    return $info->render();
  }

}}