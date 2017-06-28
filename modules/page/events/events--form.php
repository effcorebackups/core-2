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
        $c_element_id = $elements['fieldset_main/field_bg_color']->child_insert(
          new markup('input', [
            'type'  => 'radio',
            'name'  => 'bg_color',
            'value' => $c_color_id,
            'title' => $c_color_id.' ('.$c_color_info->value.')',
            'style' => ['background-color: '.$c_color_info->value]])
        );
        if ($c_color_id == $decoration['page']->background_color) {
          $elements['fieldset_main/field_bg_color']->child_select($c_element_id)->attribute_insert('checked', 'checked');
        }
      }
    }
  }

  static function on_submit_admin_decoration($form, $elements) {
    storages::get('settings')->changes_register_action('page', (object)[
      'action' => 'update',
      'npath'  => 'decoration/page/background_color',
      'value'  => $_POST['bg_color']
    ]);
  }

}}