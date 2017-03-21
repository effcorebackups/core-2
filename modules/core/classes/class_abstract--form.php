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
            foreach (factory::collect_content($c_form->content) as $c_element) {
              $c_name = isset($c_element->properties->name) ? $c_element->properties->name : null;
              $c_type = isset($c_element->properties->type) ? $c_element->properties->type : null;
              $c_form_value = isset($c_element->properties->value) ? $c_element->properties->value : null;
              $c_post_value = isset($_POST[$c_name]) ? $_POST[$c_name] : null; # @todo: check security risks
              if ($c_name && $c_name != 'op' && $c_post_value) {
                  $c_element->properties->value = $c_post_value;
                  switch ($c_type) {
                    case 'email':
                      if (!filter_var($c_post_value, FILTER_VALIDATE_EMAIL)) {
                        static::$errors[$form_id][$c_name][] = 'Field '.$c_name.' contains an invalid email address!';
                      }
                      break;
                  }
              }
            }
          }
        # render form elements
          $r_content = [];
          foreach ($c_form->content as $c_element) {
            $r_content[] = method_exists($c_element, 'render') ?
                                         $c_element->render() :
                                         $c_element;
          }
        # add form_id field
          $r_content[] = (new html('input', ['type' => 'hidden', 'name' => 'form_id', 'value' => $form_id]))->render();
        # return rendered form
          return (new html('form', (array)$c_form->properties, $r_content))->render();
        }
      } 
    }
  }

}}