<?php

namespace effectivecore {
          abstract class form {

  static $errors = [];

  static function build($form_id) {
    foreach (settings::$data['forms'] as $c_forms) {
      foreach ($c_forms as $c_form) {
        if ($c_form->id == $form_id) {
        # set and validate new values after submit
          if (isset($_POST['form_id']) &&
                    $_POST['form_id'] == $form_id) {
            $content = factory::collect_content($c_form->content);
            foreach ($content as $element_id => $c_element) {
              $c_name = isset($c_element->properties->name) ? $c_element->properties->name : '';
              $c_type = isset($c_element->properties->type) ? $c_element->properties->type : '';
              $c_form_value = isset($c_element->properties->value) ? $c_element->properties->value : '';
              $c_post_value = isset($_POST[$c_name]) ? $_POST[$c_name] : ''; # @todo: check security risks
              if ($c_name && $c_name != 'op') {
                  $c_element->properties->value = $c_post_value;
              # check email field
                switch ($c_type) {
                  case 'email':
                    if ($c_post_value && !filter_var($c_post_value, FILTER_VALIDATE_EMAIL)) {
                      static::$errors[$form_id][$element_id][] = 'Field '.$c_name.' contains an invalid email address!';
                    }
                    break;
                }
              # check required fields
                if (isset($c_element->properties->required)) {
                  if (strlen($c_post_value) == 0) {
                    static::$errors[$form_id][$element_id][] = 'Field '.$c_name.' can not be blank!';
                  }
                }
              # check max length
                if (isset($c_element->properties->maxlength)) {
                  if (strlen($c_post_value) > $c_element->properties->maxlength) {
                    static::$errors[$form_id][$element_id][] = 'Field '.$c_name.' contain too much symbols! Maximum '.$c_element->properties->maxlength.' symbols.';
                  }
                }
              # check min length
                if (isset($c_element->properties->minlength)) {
                  if (strlen($c_post_value) < $c_element->properties->minlength) {
                    static::$errors[$form_id][$element_id][] = 'Field '.$c_name.' contain too few symbols! Minimum '.$c_element->properties->minlength.' symbols.';
                  }
                }
              }
            }
          # show errors and set error class
            if (isset(static::$errors[$form_id])) {
              foreach (static::$errors[$form_id] as $element_id => $c_errors) {
                if (!isset($content[$element_id]->properties->class)) $content[$element_id]->properties->class = '';
                $content[$element_id]->properties->class.= ' error';
                foreach ($c_errors as $c_error) {
                  message::set($c_error, 'error');
                }
              }
          # or call submit handler if no errors
            } else {
              call_user_func_array($c_form->on_submit->handler, [$_POST]);
            }
          }
        # render form elements
          $r_content = [new html('input', ['type' => 'hidden', 'name' => 'form_id', 'value' => $form_id])];
          foreach ($c_form->content as $c_element) {
            $r_content[] = method_exists($c_element, 'render') ?
                                         $c_element->render() :
                                         $c_element;
          }
        # return rendered form
          return (new html('form', (array)$c_form->properties, $r_content))->render();
        }
      } 
    }
  }

}}