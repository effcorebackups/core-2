<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\markup;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_block_demo_dynamic() {
    $block = new markup('div', ['id' => 'block_demo_dynamic', 'class' => ['block']]);
    $block->child_insert(new markup('h2', [], 'Dynamic block'));
    $block->child_insert(new markup('div', [],
      'test test test test test test test test test test test test test test test'.
      'test test test test test test test test test test test test test test test'.
      'test test test test test test test test test test test test test test test'.
      'test test test test test test test test test test test test test test test'.
      'test test test test test test test test test test test test test test test'
    ));
    return $block;
  }

}}