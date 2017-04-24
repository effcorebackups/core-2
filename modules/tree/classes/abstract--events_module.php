<?php

namespace effectivecore\modules\tree {
          use \effectivecore\factory;
          use \effectivecore\settings_factory as settings;
          abstract class events_module extends \effectivecore\events_module_factory {

  static function on_init() {
  # link all parents for tree_items
    foreach (settings::$data['tree_items'] as $c_items) {
      foreach ($c_items as $item_id => $c_item) {
        if (!empty($c_item->parent)) {
          $c_parent = factory::npath_get_object($c_item->parent, settings::$data);
          if ($c_parent) {
            $c_parent->children[$item_id] = $c_item;
          }
        }
      }
    }
  }

}}