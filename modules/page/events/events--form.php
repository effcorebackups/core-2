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
        $c_color = new markup('input', [
          'type'  => 'radio',
          'name'  => 'color',
          'value' => $c_color_id,
          'title' => $c_color_id.' ('.$c_color_info->value.')',
          'style' => ['background-color: '.$c_color_info->value]
        ]);
        $c_color_bg = new markup('input', [
          'type'  => 'radio',
          'name'  => 'color_bg',
          'value' => $c_color_id,
          'title' => $c_color_id.' ('.$c_color_info->value.')',
          'style' => ['background-color: '.$c_color_info->value]
        ]);
        if ($c_color_id == $decoration['page']->color)    $c_color->attribute_insert('checked', 'checked');
        if ($c_color_id == $decoration['page']->color_bg) $c_color_bg->attribute_insert('checked', 'checked');
        $elements['fieldset_default/field_color']->child_insert($c_color);
        $elements['fieldset_default/field_color_bg']->child_insert($c_color_bg);
      }
    }
  }

  static function on_submit_admin_decoration($form, $elements, $values) {
    storages::get('settings')->changes_register_action('page', (object)[
      'action' => 'update',
      'npath'  => 'decoration/page/color',
      'value'  => $values['color']
    ]);
    storages::get('settings')->changes_register_action('page', (object)[
      'action' => 'update',
      'npath'  => 'decoration/page/color_bg',
      'value'  => $values['color_bg']
    ]);
  }

}}