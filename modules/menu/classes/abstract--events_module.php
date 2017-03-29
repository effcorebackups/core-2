<?php

namespace effectivecore\modules\menu {
          use \effectivecore\factory;
          use \effectivecore\settings;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
  # link all parents for menu_items
    foreach (settings::$data['menu_items'] as $c_items) {
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