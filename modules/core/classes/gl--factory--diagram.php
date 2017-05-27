<?php

namespace effectivecore {
          use \effectivecore\console_factory as console;
          abstract class diagram_factory {

  static function render() {
    $statistics = [];
    $total = 0;
    foreach (console::$data as $c_log) {
      if(floatval($c_log['time'])) {
        if (!isset($statistics[$c_log['group']])) $statistics[$c_log['group']] = 0;
        $statistics[$c_log['group']] += floatval($c_log['time']);
        $total += floatval($c_log['time']);
      }
    }
    $diagram = new markup('dl', [], []);
    foreach ($statistics as $c_param => $c_value) {
      $diagram->add_child(new markup('dt', [], $c_param));
      $diagram->add_child(new markup('dd', [], [
        number_format($c_value, 6).' sec. ('.
        number_format($c_value / $total * 100, 1).'%)',
        new markup('div', [
          'class' => 'scale scale-'.factory::to_css_class($c_param),
          'style' => 'width:'.(int)($c_value / $total * 500).'px'
        ], '')
      ]));
    }
    return (new markup('diagram', [], $diagram))->render();
  }

}}