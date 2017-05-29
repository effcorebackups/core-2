<?php

namespace effectivecore\modules\tree {
          use \effectivecore\factory;
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
  # link all parents for tree_items
    foreach (storage::get('settings')->select('tree_items') as $c_items) {
      foreach ($c_items as $item_id => $c_item) {
        if (!empty($c_item->parent)) {
          $c_parent = factory::npath_get_object($c_item->parent, storage::get('settings')->select());
          if ($c_parent) {
            $c_parent->children[$item_id] = $c_item;
          }
        }
      }
    }
  }

}}