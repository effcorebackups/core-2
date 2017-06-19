<?php

namespace effectivecore\modules\page {
          use \effectivecore\markup;
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_admin_decoration($form, $elements) {
    foreach (storages::get('settings')->select('palette') as $module_id => $c_palette) {
      foreach ($c_palette as $c_color) {
        $elements['field_bg_color']->add_child(
          new markup('input', [
            'type'  => 'radio',
            'name'  => 'bg_color',
            'value' => $c_color->value,
            'title' => $c_color->value,
            'style' => 'background-color:'.$c_color->value])
        );
      }
    }
  }

  static function on_submit_admin_decoration($form, $elements) {
    messages::add_new('Test');
  }

}}