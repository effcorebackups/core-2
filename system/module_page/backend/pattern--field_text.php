<?php

  ##################################################################
  ### Copyright © 2017—2019 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          class field_text extends field {

  public $title = 'Text';
  public $attributes = ['data-type' => 'text'];
  public $element_attributes = [
    'type'      => 'text',
    'name'      => 'text',
    'required'  => true,
    'maxlength' => 255
  ];

  ###########################
  ### static declarations ###
  ###########################

  static function validate($field, $form, $npath) {
    $element = $field->child_select('element');
    $name = $field->name_get();
    $type = $field->type_get();
    if ($name && $type) {
      if ($field->disabled_get()) return true;
      if ($field->readonly_get()) return true;
      $new_value = static::request_value_get($name, static::get_cur_number($name), $form->source_get());
      $result = static::validate_required ($field, $form, $element, $new_value) &&
                static::validate_minlength($field, $form, $element, $new_value) &&
                static::validate_maxlength($field, $form, $element, $new_value) &&
                static::validate_value    ($field, $form, $element, $new_value) &&
                static::validate_pattern  ($field, $form, $element, $new_value);
      $field->value_set($new_value);
      return $result;
    }
  }

  static function validate_required($field, $form, $element, &$new_value) {
    if ($field->required_get() && strlen($new_value) == 0) {
      $field->set_error(
        'Field "%%_title" can not be blank!', ['title' => translation::get($field->title)]
      );
    } else {
      return true;
    }
  }

  static function validate_minlength($field, $form, $element, &$new_value) {
    $minlength = $field->minlength_get();
    if (strlen($new_value) && $minlength && $minlength > strlen($new_value)) {
      $field->set_error(
        'Field "%%_title" must contain a minimum of %%_number character%%_plural{number,s}!', ['title' => translation::get($field->title), 'number' => $minlength]
      );
    } else {
      return true;
    }
  }

  static function validate_maxlength($field, $form, $element, &$new_value) {
    $maxlength = $field->maxlength_get();
    if (strlen($new_value) && $maxlength && $maxlength < strlen($new_value)) {
      $new_value = substr($new_value, 0, $maxlength);
      $field->set_error(new text_multiline([
        'Field "%%_title" must contain a maximum of %%_number character%%_plural{number,s}!',
        'Value was trimmed to the required length!',
        'Check field again before submit.'], ['title' => translation::get($field->title), 'number' => $maxlength]
      ));
    } else {
      return true;
    }
  }

  static function validate_min($field, $form, $element, &$new_value) {
    $min = $field->min_get();
    if (strlen($new_value) && $min && $new_value < $min) {
      $field->set_error(new text_multiline([
        'Field "%%_title" contains incorrect value!',
        'Field value is less than %%_value.'], ['title' => translation::get($field->title), 'value' => $min]
      ));
    } else {
      return true;
    }
  }

  static function validate_max($field, $form, $element, &$new_value) {
    $max = $field->max_get();
    if (strlen($new_value) && $max && $new_value > $max) {
      $field->set_error(new text_multiline([
        'Field "%%_title" contains incorrect value!',
        'Field value is more than %%_value.'], ['title' => translation::get($field->title), 'value' => $max]
      ));
    } else {
      return true;
    }
  }

  static function validate_pattern($field, $form, $element, &$new_value) {
    $pattern = $field->pattern_get();
    if (strlen($new_value) && $pattern &&
              !preg_match('%'.$pattern.'%', $new_value)) {
      $field->set_error(new text_multiline([
        'Field "%%_title" contains incorrect value!',
        'Field value does not match the regular expression %%_expression.'], ['title' => translation::get($field->title), 'expression' => $pattern]
      ));
    } else {
      return true;
    }
  }

  static function validate_value($field, $form, $element, &$new_value) {
    return true;
  }

}}