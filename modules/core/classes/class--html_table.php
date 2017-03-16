<?php

namespace effectivecore {
          class html_table extends html {

  function __construct($attr = [], $body, $head = []) {
    parent::__construct('table', $attr);
    if ($head) {
      $thead = new html('thead', [], new html('tr', []));
      foreach ($head as $cell) $thead->tr[0]->add_element(new html('th', [], $cell));
      $this->add_element($thead);
    }
    if ($body == []) {
      $body = [[['_attr' => ['class' => ['no-items']], 'no items']]];
    }
    foreach ($body as $row) {
      $tr = new html('tr', isset($row['_attr']) ? $row['_attr'] : []);
      unset($row['_attr']);
      foreach ($row as $cell) {
        $c_cell_attr = isset($cell['_attr']) ? $cell['_attr'] : [];
        if (isset($cell['_attr'])) unset($cell['_attr']);
        $td = new html('td', $c_cell_attr, $cell);
        $tr->add_element($td);
      }
      $this->add_element($tr);
    }
  }

}}