<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\markup;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_admin_decoration($form, $fields) {
    $decoration = storages::get('settings')->select('decoration');
    $fields['fieldset_default/field_color_id'   ]->default_set($decoration['page']->color_id);
    $fields['fieldset_default/field_color_bg_id']->default_set($decoration['page']->color_bg_id);
  }

  static function on_submit_admin_decoration($form, $fields, &$values) {
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_id',    $values['color_id'], false);
    storages::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_bg_id', $values['color_bg_id']);
    messages::add_new('Changes was saved.');
  }

}}