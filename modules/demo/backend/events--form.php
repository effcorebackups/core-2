<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\demo {
          use \effectivecore\messages_factory as messages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_demo($form, $elements) {
    $elements['fieldset_default/field_select_macro']->value_insert('Option 1.5 (inserted from init)', 'option_1_5', [], 'group_1_1');
    $elements['fieldset_default/field_select_macro']->value_insert('Option 1.6 (inserted + disabled from init)', 'option_1_6', ['disabled' => 'disabled'], 'group_1_1');
    $elements['fieldset_default/field_select_macro']->group_insert('group_1_2', 'Group 1.2 (inserted from init)');
    $elements['fieldset_default/field_select_macro']->value_insert('Option 1.7 (inserted from init)', 'option_1_7', [], 'group_1_2');
    $elements['fieldset_default/field_select_macro']->value_insert('Option 1.8 (inserted from init)', 'option_1_8', [], 'group_1_2');
    $elements['fieldset_default/field_select_macro']->value_insert('Option 1.9 (inserted from init)', 'option_1_9', [], 'group_1_2');
    $elements['fieldset_default/field_select_multiple_macro']->value_insert('Option 2.5 (inserted from init)', 'option_2_5', [], 'group_2_1');
    $elements['fieldset_default/field_select_multiple_macro']->value_insert('Option 2.6 (inserted + disabled from init)', 'option_2_6', ['disabled' => 'disabled'], 'group_2_1');
    $elements['fieldset_default/field_select_multiple_macro']->group_insert('group_2_2', 'Group 2.2 (inserted from init)');
    $elements['fieldset_default/field_select_multiple_macro']->value_insert('Option 2.7 (inserted from init)', 'option_2_7', [], 'group_2_2');
    $elements['fieldset_default/field_select_multiple_macro']->value_insert('Option 2.8 (inserted from init)', 'option_2_8', [], 'group_2_2');
    $elements['fieldset_default/field_select_multiple_macro']->value_insert('Option 2.9 (inserted from init)', 'option_2_9', [], 'group_2_2');
  }

  static function on_submit_demo($form, $elements, &$values) {
    messages::add_new('Call \effectivecore\modules\demo\events_form::on_submit_demo.');
  }

}}