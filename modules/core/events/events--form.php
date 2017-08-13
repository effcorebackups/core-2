<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\markup;
          use \effectivecore\translate_factory as translations;
          abstract class events_form extends events {

  ###############
  ### on_init ###
  ###############

  static function on_init($page_args, $form_args, $values) {
  }

  ###################
  ### on_validate ###
  ###################

  # аttributes which controlled:
  # ─────────────────────────────────────────────────────────────────────
  # textarea             : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=text]     : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=password] : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=search]   : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=url]      : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=tel]      : +disabled, +readonly, required, minlength, maxlength, pattern
  # input[type=email]    : +disabled, +readonly, required, minlength, maxlength, pattern, multiple
  # select               :  disabled,  readonly, required, multiple
  # input[type=file]     :  disabled,  readonly, required, multiple
  # input[type=checkbox] :  disabled,  readonly, required, checked
  # input[type=radio]    :  disabled,  readonly, required, checked
  # input[type=number]   :  disabled,  readonly, required, min, max, step
  # input[type=range]    :  disabled,  readonly, required, min, max, step
  # input[type=date]     :  disabled,  readonly, required, min, max
  # input[type=time]     :  disabled,  readonly, required, min, max
  # input[type=color]    :  disabled,  readonly, required
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

        # check new value
          $c_new_value = isset($values[$c_name]) ?
                               $values[$c_name] : '';
          switch ($c_element->tag_name) {

            case 'select':
              if ($c_new_value) {
              # delete default (from init) and set new (from post) SELECTED state
                foreach ($c_element->child_select_all() as $c_option) {
                  if ($c_option instanceof node && $c_option->tag_name == 'option') {
                    $c_option->attribute_delete('selected');
                    $c_option_value = $c_option->attribute_select('value');
                    $c_new_values = factory::array_values_map_to_keys(is_array($c_new_value) ? $c_new_value : [$c_new_value]);
                    if (isset($c_new_values[$c_option_value])) {
                      $c_option->attribute_insert('selected', 'selected');
                    }
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
    if ($element->attribute_select('required') && $new_value == '') {
      $form->add_error($id,
        translations::get('Field "%%_title" can not be blank!', ['title' => $title])
      );
      return false;
    }

  # check minimum length
    if ($element->attribute_select('minlength') &&
        $element->attribute_select('minlength') > strlen($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too few symbols!', ['title' => $title]).br.
        translations::get('Minimum %%_value symbols.', ['value' => $element->attribute_select('minlength')])
      );
      return false;
    }

  # check maximum length
    if ($element->attribute_select('maxlength') &&
        $element->attribute_select('maxlength') < strlen($new_value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too much symbols!', ['title' => $title]).br.
        translations::get('Maximum %%_value symbols.', ['value' => $element->attribute_select('maxlength')]).br.
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

  static function on_submit($page_args, $form_args, $values) {
  }

}}