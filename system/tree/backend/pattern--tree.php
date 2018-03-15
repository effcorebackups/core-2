<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          class tree extends \effcore\node {

  public $id;
  public $title = '';
  public $template = 'tree';

  function __construct($title = '', $attributes = [], $children = [], $weight = 0) {
    if ($title) $this->title = $title;
    parent::__construct($attributes, $children, $weight);
  }

  ######################
  ### static methods ###
  ######################

  static protected $cache_trees;
  static protected $cache_tree_items;

  static function init() {
    $trees      = storage::get('files')->select('trees');
    $tree_items = storage::get('files')->select('tree_items');
    foreach ($trees as $c_module_id => $c_module_trees) {
      foreach ($c_module_trees as $c_row_id => $c_tree) {
        static::$cache_trees[$c_tree->id] = $c_tree;
      }
    }
    foreach ($tree_items as $c_module_id => $c_module_tree_items) {
      foreach ($c_module_tree_items as $c_row_id => $c_item) {
        static::$cache_tree_items[$c_item->id] = $c_item;
      }
    }
  }

  static function get($id = null) {
    return $id ? static::$cache_trees[$id] :
                 static::$cache_trees;
  }

  static function get_item($id = null) {
    return $id ? static::$cache_tree_items[$id] :
                 static::$cache_tree_items;
  }

}}