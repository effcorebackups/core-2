<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\markup;
          use \effectivecore\table as table;
          use \effectivecore\table_body_row as table_body_row;
          use \effectivecore\table_body_row_cell as table_body_row_cell;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_block_demo_dynamic() {
    $block = new markup('x-block', ['id' => 'demo_dynamic']);
  # title
    $block->child_insert(
      new markup('h2', [], 'Dynamic block')
    );
  # table
    $thead = [['th 1', 'th 2', 'th 3']];
    $tbody = [
      ['td 1.1', 'td 1.2', 'td 1.3'],
      ['td 2.1', 'td 2.2', new table_body_row_cell([], 'td 2.3')],
      new table_body_row([], ['td 3.1', 'td 3.2', new table_body_row_cell([], 'td 3.3')])
    ];
    $block->child_insert(
      new table(['class' => ['table' => 'table']], $tbody, $thead)
    );
    return $block;
  }

}}