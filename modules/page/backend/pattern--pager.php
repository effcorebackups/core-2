<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class pager extends \effectivecore\markup {

  public $id;
  public $tag_name = 'x-pager';
  public $has_error = false;

  function __construct($attributes = [], $weight = 0) {
    parent::__construct($attributes, [], $weight);
  }

  function get_current_page_num() {
    return 1;
  }

  function render() {
    $pager = new markup($this->tag_name);
    $pager->child_insert(new markup('a', ['href' => '#'], new text(1)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text('...')));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(25)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text('...')));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(47)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(48)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(49)));
    $pager->child_insert(new markup('a', ['href' => '#', 'class' => ['active']], new text(50)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(51)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(52)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(53)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text('...')));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(75)));
    $pager->child_insert(new markup('a', ['href' => '#'], new text('...')));
    $pager->child_insert(new markup('a', ['href' => '#'], new text(100)));
    return $pager->render();
  }

}}