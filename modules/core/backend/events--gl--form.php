<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\markup;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          abstract class events_form {

  ###############
  ### on_init ###
  ###############

  static function on_init($form, $elements) {
  }

  ###################
  ### on_validate ###
  ###################

  # attributes support:
  # ─────────────────────────────────────────────────────────────────────
  # - textarea                   : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=text]           : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=password]       : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=search]         : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=url]            : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=tel]            : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # - input[type=email]          : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern, multiple
  # - select                     : DISABLED,           REQUIRED, MULTIPLE
  # - select::option             : DISABLED
  # - input[type=file]           : disabled, readonly, required, multiple
  # - input[type=checkbox]       : disabled, readonly, required, checked
  # - input[type=radio]          : disabled, readonly, required, checked
  # - input[type=number]         : disabled, readonly, required, min, max, step
  # - input[type=range]          : disabled, readonly, required, min, max, step
  # - input[type=date]           : disabled, readonly, required, min, max
  # - input[type=time]           : disabled, readonly, required, min, max
  # - input[type=color]          : disabled, readonly, required
  # ─────────────────────────────────────────────────────────────────────
  # - input[type=hidden]         : not processed element
  # - input[type=button]         : not processed element
  # - input[type=reset]          : not processed element
  # - input[type=submit]         : not processed element
  # - input[type=image]          : not processed element
  # - input[type=week]           : not processed element
  # - input[type=month]          : not processed element
  # - input[type=datetime]       : not processed element
  # - input[type=datetime-local] : not processed element
  # ─────────────────────────────────────────────────────────────────────

  # attributes validation plan:
  # ─────────────────────────────────────────────────────────────────────
  # - DISABLED                   : disable any processing of element
  # - READONLY                   : disable any processing of element
  # - REQUIRED                   : VALUE != '' (value must be present in $_POST)
  # - MINLENGTH                  : VALUE >= MINLENGTH
  # - MAXLENGTH                  : VALUE <= MAXLENGTH
  # - MIN                        : VALUE >= MIN (for date|time should compare timestamps)
  # - MAX                        : VALUE <= MAX (for date|time should compare timestamps)
  # - STEP                       : VALUE should be in valid step range: MIN + STEP * N, where N = [0, 1, 2 ...]
  # - PATTERN                    : VALUE should match the PATTERN (used FILTER_VALIDATE_REGEXP)
  # - MULTIPLE                   : VALUE must be singular if MULTIPLE attribute is not present
  # ─────────────────────────────────────────────────────────────────────
  # - input[type=email]          : VALUE should filtered via FILTER_VALIDATE_EMAIL
  # - input[type=url]            : VALUE should filtered via FILTER_VALIDATE_URL
  # - input[type=date]           : VALUE should match the pattern YYYY-MM-DD
  # - input[type=time]           : VALUE should match the pattern HH:MM:SS|HH:MM
  # - input[type=color]          : VALUE should match the pattern #dddddd
  # ─────────────────────────────────────────────────────────────────────

  static function on_validate($form, $elements, &$values) {
    foreach ($elements as $c_id => $c_element) {
      if ($c_element instanceof node) {
        $c_name = rtrim($c_element->attribute_select('name'), '[]');
        $c_type =       $c_element->attribute_select('type');
        if ($c_name) {

        # disable processing if element disabled or readonly
          if ($c_element->attribute_select('disabled') ||
              $c_element->attribute_select('readonly')) {
            continue;
          }

        # conversion matrix for value from text field (expected: undefined|string):
        # ─────────────────────────────────────────────────────────────────────
        # - unset($_POST[name])                 -> ''
        # - $_POST[name] == ''                  -> ''
        # - $_POST[name] == 'value'             -> 'value'
        # ─────────────────────────────────────────────────────────────────────

          if ($c_element->tag_name == 'textarea' ||
              $c_element->tag_name == 'input') {
            $c_new_text_value = isset($values[$c_name]) ?
                                      $values[$c_name] : '';
          }

        # conversion matrix for value from singular select (expected: undefined|string):
        # ─────────────────────────────────────────────────────────────────────
        # - unset($_POST[name])                 -> []
        # - $_POST[name] == ''                  -> ['' => '']
        # - $_POST[name] == 'value'             -> ['value' => 'value']

        # conversion matrix for values from multiple select (expected: undefined|array):
        # ─────────────────────────────────────────────────────────────────────
        # - unset($_POST[name])                 -> []
        # - $_POST[name] == [0 => '']           -> ['' => '']
        # - $_POST[name] == [0 => '', ...]      -> ['' => '', ...]
        # - $_POST[name] == [0 => 'value']      -> ['value' => 'value']
        # - $_POST[name] == [0 => 'value', ...] -> ['value' => 'value', ...]
        # ─────────────────────────────────────────────────────────────────────

          if ($c_element->tag_name == 'select') {
            $c_new_select_values = factory::array_values_map_to_keys(
                  !isset($values[$c_name]) ? [] :
               (is_array($values[$c_name]) ?
                         $values[$c_name]  :
                        [$values[$c_name]]));
          }

        # select validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'select') {
          # collect allowed values
            $c_allowed_values = [];
            foreach ($c_element->child_select_all() as $c_option) {
              if ($c_option instanceof node && $c_option->tag_name == 'option') {
                if (!$c_option->attribute_select('disabled')) {
                  $c_allowed_values[$c_option->attribute_select('value')] =
                                    $c_option->attribute_select('value');
                }
              }
            }
            static::_validate_field_selector($form, $c_element, $c_id,
              $c_new_select_values, $c_allowed_values
            );
          # set new values after validation
            foreach ($c_element->child_select_all() as $c_option) {
              if ($c_option instanceof node && $c_option->tag_name == 'option') {
                $c_option->attribute_delete('selected');
                if (isset($c_new_select_values[$c_option->attribute_select('value')])) {
                  $c_option->attribute_insert('selected', 'selected');
                }
              }
            }
          }

        # textarea validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'textarea') {
            static::_validate_field_text($form, $c_element, $c_id, $c_new_text_value);
            $content = $c_element->child_select('content');
            $content->text = $c_new_text_value;
          }

        # input[type=file] validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'input' &&
              $c_type == 'file') {
            # @todo: make functionality
          }

        # input[type=checkbox] validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'input' &&
              $c_type == 'checkbox') {
            if ($c_new_text_value) {
              $c_element->attribute_insert('checked', 'checked');
            }
          }

        # input[type=radio] validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'input' &&
              $c_type == 'radio') {
          # delete default (from _init) and set new (from $_POST) CHECKED state
            if  ($c_element->attribute_select('value') == $c_new_text_value)
                 $c_element->attribute_insert('checked', 'checked');
            else $c_element->attribute_delete('checked');
          }

        # input[type=text|password|search|email|url|tel|number|range|date|time|color] validation:
        # ─────────────────────────────────────────────────────────────────────
          if ($c_element->tag_name == 'input' && (
              $c_type == 'text'     ||
              $c_type == 'password' ||
              $c_type == 'search'   ||
              $c_type == 'url'      ||
              $c_type == 'tel'      ||
              $c_type == 'email'    ||
              $c_type == 'number'   ||
              $c_type == 'range'    ||
              $c_type == 'date'     ||
              $c_type == 'time'     ||
              $c_type == 'color')) {
            static::_validate_field_text($form, $c_element, $c_id, $c_new_text_value);
            $c_element->attribute_insert('value', $c_new_text_value);
          }

        }
      }
    }
  }

  ################################
  ### _validate_field_selector ###
  ################################

  static function _validate_field_selector($form, $element, $id, &$new_values, $allowed_values) {
    $title = translations::get(
      $element->title
    );

  # convert array with empty strings to array without empty strings:
  # ─────────────────────────────────────────────────────────────────────
  # - []                        -> []
  # - ['' => '']                -> []
  # - ['' => '', ...]           -> [...]
  # - ['value' => 'value']      -> ['value' => 'value']
  # - ['value' => 'value', ...] -> ['value' => 'value', ...]
  # ─────────────────────────────────────────────────────────────────────

    if ($element->attribute_select('required') && empty(array_filter($new_values, 'strlen'))) {
      $form->add_error($id,
        translations::get('Field "%%_title" must be selected!', ['title' => $title])
      );
      return;
    }

  # normalize not empty array:
  # ─────────────────────────────────────────────────────────────────────
  # - ['' => '', ...]           -> [...]
  # ─────────────────────────────────────────────────────────────────────

    if (isset($new_values['']) &&
        count($new_values) > 1)
        unset($new_values['']);

  # deleting fake values from the user's side
  # deleting DISABLED values
    $new_values = array_intersect($new_values, $allowed_values);

  # check if field is multiple or singular
    if (!$element->attribute_select('multiple') && count($new_values) > 1) {
      $new_values = array_slice($new_values, 0, 1);
      $form->add_error($id,
        translations::get('Field "%%_title" is not support multiple select!', ['title' => $title])
      );
    }
  }

  ############################
  ### _validate_field_text ###
  ############################

  static function _validate_field_text($form, $element, $id, &$new_value) {
    $title = translations::get(
      $element->title
    );

  # check required fields
    if ($element->attribute_select('required') && strlen($new_value) == 0) {
      $form->add_error($id,
        translations::get('Field "%%_title" can not be blank!', ['title' => $title])
      );
      return;
    }

  # check minimum length
    if ($element->attribute_select('minlength') &&
        $element->attribute_select('minlength') > strlen($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too few characters!', ['title' => $title]).br.
        translations::get('Must be at least %%_value characters long.', ['value' => $element->attribute_select('minlength')])
      );
      return;
    }

  # check maximum length
    if ($element->attribute_select('maxlength') &&
        $element->attribute_select('maxlength') < strlen($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too much characters!', ['title' => $title]).br.
        translations::get('Must be no more than %%_value characters.', ['value' => $element->attribute_select('maxlength')]).br.
        translations::get('The value was trimmed to the required length!').br.
        translations::get('Check field again before submit.')
      );
    # trim value to maximum lenght
      $new_value = substr($new_value, 0, $element->attribute_select('maxlength'));
      return;
    }

  # check email field
    if ($element->attribute_select('type') == 'email' &&
        filter_var($new_value, FILTER_VALIDATE_EMAIL) == false) {
      $form->add_error($id,
        translations::get('Field "%%_title" contains an invalid email address!', ['title' => $title])
      );
      return;
    }
  }

  #################
  ### on_submit ###
  #################

  static function on_submit_install($form, $elements, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        foreach (static::get()->on_module_install as $c_event) call_user_func($c_event->handler);
        messages::add_new('Modules was installed.');
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/');
        break;
    }
  }

}}