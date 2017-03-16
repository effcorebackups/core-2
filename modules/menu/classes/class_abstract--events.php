<?php

namespace effectivecore\modules\menu {
          use \effectivecore\settings;
          use \effectivecore\html;
          use \effectivecore\modules\user\access;
          abstract class events {

  static function on_block_menus() {
  # collect all menu items
    $menus = new menu_item('menu');
    foreach (settings::$data['menu_items'] as $c_grp_items) {
      foreach ($c_grp_items as $c_item) {
        if (!isset($c_item->access) ||
            (isset($c_item->access) && access::check($c_item->access))) {
          $menus->add_child($c_item->root, isset(
                            $c_item->title) ? $c_item->title : '', isset(
                            $c_item->properties) ? $c_item->properties : null, isset(
                            $c_item->weight) ? $c_item->weight : 0);
        }
      }
    }
  # render all menus
    $output = '';
    foreach ($menus->children as $name => $data) {
      $output.= (new html('menu', ['id' => 'menu_'.$name], new html('ul', [], $data->children)))->render();
    }
    return $output;
  }

}}