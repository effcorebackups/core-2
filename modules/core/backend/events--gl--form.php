<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\markup;
          use \effectivecore\translations_factory as translations;
          abstract class events_form extends events {

  ###############
  ### on_init ###
  ###############

  static function on_init($form, $elements) {
  }

  ###################
  ### on_validate ###
  ###################

  # аttributes which controlled:
  # ─────────────────────────────────────────────────────────────────────
  # textarea             : DISABLED, READONLY, REQUIRED, MINLENGTH, MAXLENGTH, pattern
  # input[type=text]     : DISABLED, READONLY, required, minlength, maxlength, pattern
  # input[type=password] : DISABLED, READONLY, required, minlength, maxlength, pattern
  # input[type=search]   : DISABLED, READONLY, required, minlength, maxlength, pattern
  # input[type=url]      : DISABLED, READONLY, required, minlength, maxlength, pattern
  # input[type=tel]      : DISABLED, READONLY, required, minlength, maxlength, pattern
  # input[type=email]    : DISABLED, READONLY, required, minlength, maxlength, pattern, multiple
  # select               : disabled,           required, multiple
  # option               : disabled
  # input[type=file]     : disabled, readonly, required, multiple
  # input[type=checkbox] : disabled, readonly, required, checked
  # input[type=radio]    : disabled, readonly, required, checked
  # input[type=number]   : disabled, readonly, required, min, max, step
  # input[type=range]    : disabled, readonly, required, min, max, step
  # input[type=date]     : disabled, readonly, required, min, max
  # input[type=time]     : disabled, readonly, required, min, max
  # input[type=color]    : disabled, readonly, required
  # ─────────────────────────────────────────────────────────────────────

  # attributes validation plan:
  # ─────────────────────────────────────────────────────────────────────
  # DISABLED             : disable any processing of element
  # READONLY             : disable any processing of element
  # REQUIRED             : VALUE != '' (value must be present in $_POST)
  # MINLENGTH            : VALUE >= MINLENGTH
  # MAXLENGTH            : VALUE <= MAXLENGTH
  # MIN                  : VALUE >= MIN
  # MAX                  : VALUE <= MAX
  # STEP                 : VALUE + STEP should filtered via FILTER_VALIDATE_INT
  # PATTERN              : VALUE should match the PATTERN (used FILTER_VALIDATE_REGEXP)
  # MULTIPLE             : VALUE must be singular if MULTIPLE attribute is not present
  # input[type=email]    : VALUE should filtered via FILTER_VALIDATE_EMAIL
  # input[type=url]      : VALUE should filtered via FILTER_VALIDATE_URL
  # input[type=date]     : VALUE should match the pattern YYYY-MM-DD
  # input[type=time]     : VALUE should match the pattern HH:MM:SS|HH:MM
  # input[type=color]    : VALUE should match the pattern #dddddd
  # ─────────────────────────────────────────────────────────────────────

  static function on_validate($form, $elements, $values) {
    foreach ($elements as $c_id => $c_element) {
      if ($c_element instanceof node) {
        $c_name = rtrim($c_element->attribute_select('name'), '[]');
        if ($c_name) {

        # disable processing if element disabled or readonly
          if ($c_element->attribute_select('disabled') ||
              $c_element->attribute_select('readonly')) {
            continue;
          }

        # check new value (string|array)
          $c_new_value = isset($values[$c_name]) ?
                               $values[$c_name] : '';

        # elements validation
          switch ($c_element->tag_name) {

            case 'select':
            # --------------------------------------------------
            # expected values for singular select: '' | 'value'
            # expected values for multiple select: '' | [''] | ['', 'value1' ...] | ['value1', 'value2' ...]
            # --------------------------------------------------
              if ($c_new_value === '')          $c_new_values = [];
              else if (is_string($c_new_value)) $c_new_values = [$c_new_value => $c_new_value];
              else if (is_array($c_new_value))  $c_new_values = factory::array_values_map_to_keys($c_new_value);
            # check values. convert [''] to [] and ['', 'value1' ...] to ['value1' ...]
              $c_chk_values = array_filter($c_new_values, 'strlen');
              static::_validate_field($form, $c_element, $c_id, $c_chk_values);
            # delete default (from init) and set new (from post) SELECTED state
              foreach ($c_element->child_select_all() as $c_option) {
                if ($c_option instanceof node && $c_option->tag_name == 'option') {
                  $c_option->attribute_delete('selected');
                  $c_option_value = $c_option->attribute_select('value');
                  if (isset($c_new_values[$c_option_value])) {
                    $c_option->attribute_insert('selected', 'selected');
                  }
                }
              }
              break;

            case 'textarea':
              static::_validate_field($form, $c_element, $c_id, $c_new_value);
              $content = $c_element->child_select('content');
              $content->text = $c_new_value;
              break;

            case 'input':
              $c_type = $c_element->attribute_select('type');
              if ($c_type) {

              # input[type=submit|reset|image|button|hidden]
                if ($c_type == 'submit' ||
                    $c_type == 'reset'  ||
                    $c_type == 'image'  ||
                    $c_type == 'button' ||
                    $c_type == 'hidden'
                ) {
                # not processed elements
                  continue;
                }
  
              # input[type=file]
                if ($c_type == 'file') {
              # ... @todo: make functionality
                }
  
              # input[type=checkbox]
                if ($c_type == 'checkbox') {
                  if ($c_new_value) {
                    $c_element->attribute_insert('checked', 'checked');
                  }
                }
  
              # input[type=radio]
              # delete default (from init) and set new (from post) CHECKED state
                if ($c_type == 'radio') {
                  if  ($c_element->attribute_select('value') == $c_new_value)
                       $c_element->attribute_insert('checked', 'checked');
                  else $c_element->attribute_delete('checked');
                }
  
              # input[type=text|password|search|email|url|tel|number|range|date|time|color]
                if ($c_type == 'text'     ||
                    $c_type == 'password' ||
                    $c_type == 'search'   ||
                    $c_type == 'email'    ||
                    $c_type == 'url'      ||
                    $c_type == 'tel'      ||
                    $c_type == 'number'   ||
                    $c_type == 'range'    ||
                    $c_type == 'date'     ||
                    $c_type == 'time'     ||
                    $c_type == 'color'
                ) {
                  static::_validate_field($form, $c_element, $c_id, $c_new_value);
                  $c_element->attribute_insert('value', $c_new_value);
                }

              }
              break;
          }
        }
      }
    }
  }

  static function _validate_field($form, $element, $id, &$new_value) {
    $title = translations::get(
      $element->title
    );

  # check required fields
    if ($element->attribute_select('required') && empty($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" can not be blank!', ['title' => $title])
      );
      return false;
    }

  # check minimum length
    if ($element->attribute_select('minlength') &&
        $element->attribute_select('minlength') > strlen($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too few characters!', ['title' => $title]).br.
        translations::get('Must be at least %%_value characters long.', ['value' => $element->attribute_select('minlength')])
      );
      return false;
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
      return false;
    }

  # check email field
    if ($element->attribute_select('type') == 'email' &&
        filter_var($new_value, FILTER_VALIDATE_EMAIL) == false) {
      $form->add_error($id,
        translations::get('Field "%%_title" contains an invalid email address!', ['title' => $title])
      );
      return false;
    }

  # if no errors
    return true;
  }

  #################
  ### on_submit ###
  #################

  static function on_submit($form, $elements, $values) {
  }

}}