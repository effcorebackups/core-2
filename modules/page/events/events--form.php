<?php

namespace effectivecore\modules\page {
          use \effectivecore\markup;
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_admin_decoration($form, $elements) {
    $decoration = storages::get('settings')->select('decoration');
    $elements['fieldset_default/field_color'   ]->default_set($decoration['page']->color);
    $elements['fieldset_default/field_color_bg']->default_set($decoration['page']->color_bg);
  }

  static function on_submit_admin_decoration($form, $elements, $values) {
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color',    $values['color']);
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_bg', $values['color_bg']);
    messages::add_new('Changes was saved.');
  }

}}