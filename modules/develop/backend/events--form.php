<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\messages_factory as messages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_demo($form, $fields) {
    $fields['fieldset_default/field_select_macro']->option_insert('Option 1.5 (inserted from init)', 'option_1_5', [], 'group_1_1');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 1.6 (inserted + disabled from init)', 'option_1_6', ['disabled' => 'disabled'], 'group_1_1');
    $fields['fieldset_default/field_select_macro']->optgroup_insert('group_1_2', 'Group 1.2 (inserted from init)');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 1.7 (inserted from init)', 'option_1_7', [], 'group_1_2');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 1.8 (inserted from init)', 'option_1_8', [], 'group_1_2');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 1.9 (inserted from init)', 'option_1_9', [], 'group_1_2');
  }

  static function on_submit_demo($form, $fields, &$values) {
    messages::add_new('Call \effectivecore\modules\develop\events_form::on_submit_demo.');
  }

}}