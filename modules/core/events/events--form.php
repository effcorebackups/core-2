<?php

namespace effectivecore {
          use \effectivecore\markup;
          use \effectivecore\translate_factory as translations;
          abstract class events_form extends events {

  static function on_init($page_args, $form_args, $values) {}
  static function on_submit($page_args, $form_args, $values) {}

  static function on_validate($form, $elements, $values) {
    foreach ($elements as $c_id => $c_element) {
      if ($c_element instanceof node) {
        $c_name = $c_element->attribute_select('name');
        $c_value = isset($values[$c_name]) ?
                         $values[$c_name] : '';
        if ($c_name) {
          switch ($c_element->tag_name) {

            case 'select':
            # ... @todo: make functionality
              break;

            case 'textarea':
              static::_validate_field($form, $c_element, $c_id, $c_value);
              $content = $c_element->child_select('content');
              $content->text = $c_value;
              break;

            case 'input':
              $c_type = $c_element->attribute_select('type');
              if ($c_type) {

              # not supported elements
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
                  if ($c_element->attribute_select('value') == $c_value) {
                    $c_element->attribute_insert('checked', 'checked');
                  }
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

}}