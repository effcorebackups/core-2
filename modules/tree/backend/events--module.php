<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\tree {
          use \effectivecore\factory;
          use \effectivecore\tree_factory as tree;
          use \effectivecore\message_factory as message;
          use \effectivecore\entity_factory as entity;
          use \effectivecore\translation_factory as translation;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    tree::init();
    foreach(tree::get_tree_items() as $c_item) {
      if ($c_item->id_parent) {
        $c_parent = !empty($c_item->parent_is_tree) ?
           tree::get_tree($c_item->id_parent) :
           tree::get_tree_item($c_item->id_parent);
        $c_parent->child_insert($c_item, $c_item->id);
      }
    };
  }

  static function on_install() {
  # install entities
    foreach (entity::select_all_by_module('tree') as $c_entity) {
      if ($c_entity->install()) message::insert(translation::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      message::insert(translation::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}