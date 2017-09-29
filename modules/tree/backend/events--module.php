<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\tree {
          use \effectivecore\factory;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
  # link all parents for tree_items
    foreach (storages::get('settings')->select_group('tree_items') as $c_items) {
      foreach ($c_items as $item_id => $c_item) {
        if (!empty($c_item->parent_npath)) {
          $c_parent = storages::get('settings')->select_by_npath($c_item->parent_npath);
          if ($c_parent) {
            $c_parent->children[$item_id] = $c_item;
          }
        }
      }
    }
  }

  static function on_install() {
    foreach (storages::get('settings')->select_group('entities')['tree'] as $c_entity) $c_entity->install();
    messages::add_new(
      translations::get('Tables for module %%_name was installed.', ['name' => 'tree'])
    );
  }

}}