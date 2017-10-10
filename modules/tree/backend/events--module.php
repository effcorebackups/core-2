<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\tree {
          use \effectivecore\factory;
          use \effectivecore\trees_factory as trees;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    trees::init();
    foreach(trees::get_tree_items() as $c_item) {
      if ($c_item->parent_id) {
        $c_parent = !empty($c_item->parent_is_tree) ?
           trees::get_tree($c_item->parent_id) :
           trees::get_tree_item($c_item->parent_id);
        $c_parent->child_insert($c_item, $c_item->id);
      }
    };
  }

  static function on_install() {
    foreach (storages::get('settings')->select_group('entities')['tree'] as $c_entity) {
      $c_entity->install();
    }
    messages::add_new(
      translations::get('Tables for module %%_name was installed.', ['name' => 'tree'])
    );
  }

}}