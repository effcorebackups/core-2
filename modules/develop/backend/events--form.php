<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\messages_factory as messages;
          abstract class events_form extends \effectivecore\events_form {

  ##################
  ### form: demo ###
  ##################

  static function on_init_demo($form, $fields) {
    $fields['fieldset_default/field_select_macro']->option_insert('Option 2.5 (inserted + disabled from init)', 'option_2_5', ['disabled' => 'disabled'], 'group_2_1');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 2.6 (inserted from init)', 'option_2_6', [], 'group_2_1');
    $fields['fieldset_default/field_select_macro']->optgroup_insert('group_2_2', 'Group 2.2 (inserted from init)');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 2.7 (inserted from init)', 'option_2_7', [], 'group_2_2');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 2.8 (inserted from init)', 'option_2_8', [], 'group_2_2');
    $fields['fieldset_default/field_select_macro']->option_insert('Option 2.9 (inserted from init)', 'option_2_9', [], 'group_2_2');
  }

  static function on_submit_demo($form, $fields, &$values) {
    messages::add_new('Call \effectivecore\modules\develop\events_form::on_submit_demo.');
  }

}}