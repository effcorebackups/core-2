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

  # attributes validation:
  # ---------------------------------------------------------------------------------
  # input[type=text|password|search|email|url|tel], textarea : value >= MINLENGTH
  # input[type=text|password|search|email|url|tel], textarea : value <= MAXLENGTH
  # input[type=text|password|search|email|url|tel], textarea : value matches the PATTERN (p.s. replaces default checking for url|tel)
  # input[type=*], select, textarea                          : REQUIRED (value must be present)
  # input[type=*], select, textarea                          : DISABLED (value must not be present)
  # input[type=file|email], select                           : MULTIPLE (check if value is multiple when only singular allowed)
  # input[type=number]                                       : value >= MIN, value <= MAX, value in valid STEP range
  # input[type=range]                                        : value >= MIN, value <= MAX, value in valid STEP range
  # input[type=date]                                         : value >= MIN, value <= MAX, value matches the pattern YYYY-MM-DD
  # input[type=time]                                         : value matches the pattern HH:MM:SS|HH:MM
  # input[type=color]                                        : value matches the pattern #dddddd
  # ---------------------------------------------------------------------------------

  static function on_validate($form, $elements, $values) {
    foreach ($elements as $c_id => $c_element) {
      if ($c_element instanceof node) {
        $c_name = $c_element->attribute_select('name');
        if ($c_name) {
          $c_value = isset($values[$c_name]) ?
                           $values[$c_name] : '';
          switch ($c_element->tag_name) {

            case 'select':
              static::_validate_field($form, $c_element, $c_id, $c_value);
              if ($c_value) {
                foreach ($c_element->child_select_all() as $c_option) {
                  if ($c_option instanceof node       &&
                      $c_option->tag_name == 'option' &&
                      $c_option->attribute_select('value') == $c_value) {
                    $c_option->attribute_insert('selected', 'selected');
                    break;
                  }
                }
              }
              break;

            case 'textarea':
              static::_validate_field($form, $c_element, $c_id, $c_value);
              $content = $c_element->child_select('content');
              $content->text = $c_value;
              break;

            case 'input':
              $c_type = $c_element->attribute_select('type');
              if ($c_type) {

              # not processed elements
                if ($c_type == 'submit' || # <input type="submit">
                    $c_type == 'reset'  || # <input type="reset">
                    $c_type == 'image'  || # <input type="image">
                    $c_type == 'button' || # <input type="button">
                    $c_type == 'hidden'    # <input type="hidden">
                ) {
                  continue;
                }
  
              # file
                if ($c_type == 'file') {
              # ... @todo: make functionality
                }
  
              # checkbox
                if ($c_type == 'checkbox') {
                  if ($c_value) {
                    $c_element->attribute_insert('checked', 'checked');
                  }
                }
  
              # radio
                if ($c_type == 'radio') {
                  if  ($c_element->attribute_select('value') == $c_value)
                       $c_element->attribute_insert('checked', 'checked');
                  else $c_element->attribute_delete('checked');
                }
  
              # html4 elements: text|password
              # html5 elements: search|email|url|tel|number|range|date|time|color
                if ($c_type == 'text'     || # <input type="text">
                    $c_type == 'password' || # <input type="password">
                    $c_type == 'search'   || # <input type="search">
                    $c_type == 'email'    || # <input type="email">
                    $c_type == 'url'      || # <input type="url">
                    $c_type == 'tel'      || # <input type="tel">
                    $c_type == 'number'   || # <input type="number">
                    $c_type == 'range'    || # <input type="range">
                    $c_type == 'date'     || # <input type="date">
                    $c_type == 'time'     || # <input type="time">
                    $c_type == 'color'       # <input type="color">
                ) {
                  static::_validate_field($form, $c_element, $c_id, $c_value);
                  $c_element->attribute_insert('value', $c_value);
                }

              }
              break;
          }
        }
      }
    }
  }

  static function _validate_field($form, $element, $id, &$value) {
    $title = translations::get(
      $element->title
    );

  # check required fields
    if ($element->attribute_select('required') && $value == '') {
      $form->add_error($id,
        translations::get('Field "%%_title" can not be blank!', ['title' => $title])
      );
      return false;
    }

  # check minimum length
    if ($element->attribute_select('minlength') &&
        $element->attribute_select('minlength') > strlen($value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too few symbols!', ['title' => $title]).br.
        translations::get('Minimum %%_value symbols.', ['value' => $element->attribute_select('minlength')])
      );
      return false;
    }

  # check maximum length
    if ($element->attribute_select('maxlength') &&
        $element->attribute_select('maxlength') < strlen($value)) {
      $form->add_error($id,
        translations::get('Field "%%_title" contain too much symbols!', ['title' => $title]).br.
        translations::get('Maximum %%_value symbols.', ['value' => $element->attribute_select('maxlength')]).br.
        translations::get('The value was trimmed to the required length!').br.
        translations::get('Check field again before submit.')
      );
    # trim value to maximum lenght
      $value = substr($value, 0, $element->attribute_select('maxlength'));
      return false;
    }

  # check email field
    if ($element->attribute_select('type') == 'email' &&
        filter_var($value, FILTER_VALIDATE_EMAIL) == false) {
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