<?php

namespace effectivecore\modules\page {
          use \effectivecore\markup;
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_admin_decoration($form, $elements) {
    $decoration = storages::get('settings')->select('decoration');    
    foreach (storages::get('settings')->select('colors') as $module_id => $c_colors) {
      foreach ($c_colors as $c_color_id => $c_color_info) {
        $elements['fieldset_default/field_color']->item_insert('', [
          'value' => $c_color_id,
          'title' => $c_color_id.' ('.$c_color_info->value.')',
          'style' => ['background-color: '.$c_color_info->value]
        ]);
        $elements['fieldset_default/field_color_bg']->item_insert('', [
          'value' => $c_color_id,
          'title' => $c_color_id.' ('.$c_color_info->value.')',
          'style' => ['background-color: '.$c_color_info->value]
        ]);
      }
    }
    $elements['fieldset_default/field_color'   ]->default_set($decoration['page']->color);
    $elements['fieldset_default/field_color_bg']->default_set($decoration['page']->color_bg);
  }

  static function on_submit_admin_decoration($form, $elements, $values) {
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color',    $values['color']);
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_bg', $values['color_bg']);
    messages::add_new('Changes was saved.');
  }

}}