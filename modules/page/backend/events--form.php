<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\markup;
          use \effectivecore\message_factory as message;
          use \effectivecore\modules\storage\storage as storage;
          abstract class events_form extends \effectivecore\events_form {

  ########################
  ### form: decoration ###
  ########################

  static function on_init_decoration($form, $fields) {
    $decoration = storage::get('settings')->select_group('decoration');
    $fields['colors/color_id'   ]->default_set($decoration['page']->color_id);
    $fields['colors/color_bg_id']->default_set($decoration['page']->color_bg_id);
  }

  static function on_validate_decoration($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
  }

  static function on_submit_decoration($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'save':
        storage::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_id',    $values['color_id'][0], false);
        storage::get('settings')->changes_register_action('page', 'update', 'decoration/page/color_bg_id', $values['color_bg_id'][0]);
        message::insert('Changes was saved.');
        break;
      case 'restore':
        storage::get('settings')->changes_unregister_action('page', 'update', 'decoration/page/color_id');
        storage::get('settings')->changes_unregister_action('page', 'update', 'decoration/page/color_bg_id');
      # message::insert('Changes was removed.');
        message::insert('UNDER CONSTRUCTION'); # @todo: make functionality
        break;
    }
  }

}}