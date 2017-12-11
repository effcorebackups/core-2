<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storage as storage;
          abstract class tree_factory {

  protected static $trees;
  protected static $tree_items;

  static function init() {
    $trees      = storage::get('settings')->select_group('trees');
    $tree_items = storage::get('settings')->select_group('tree_items');
    foreach ($trees as $c_trees_by_module) {
      foreach ($c_trees_by_module as $c_tree) {
        static::$trees[$c_tree->id] = $c_tree;
      }
    }
    foreach ($tree_items as $c_items_by_module) {
      foreach ($c_items_by_module as $c_item) {
        static::$tree_items[$c_item->id] = $c_item;
      }
    }
  }

  static function select_trees()        {return static::$trees;}
  static function select_tree_items()   {return static::$tree_items;}
  static function select_tree($id)      {return static::$trees[$id];}
  static function select_tree_item($id) {return static::$tree_items[$id];}

}}