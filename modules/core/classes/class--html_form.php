<?php

namespace effectivecore {
          class html_form extends html {

  public $form_id;
  public $errors = [];

  function render() {
    $elements = factory::data_to_flat($this->content);
    if (!empty($this->form_id) && !empty($_POST['form_id']) && $this->form_id === $_POST['form_id']) {
      foreach ($elements as $c_element) {
        $c_name = !empty($c_element->attr['name']) ? $c_element->attr['name'] : null;
        $c_type = !empty($c_element->attr['type']) ? $c_element->attr['type'] : null;
        $c_post_value = !empty($_POST[$c_name]) ? $_POST[$c_name] : '';
        if ($c_name && $c_type) {
          if ($c_post_value) {
          # restore posted values
            if ($c_element->attr['name'] != 'op') {
              $c_element->attr['value'] = $c_post_value; # @todo: check security risks
            }
          # check fields value
            switch ($c_type) {
              case 'email':
                if (!filter_var($c_post_value, FILTER_VALIDATE_EMAIL)) {
                  $this->errors[$c_name][] = 'Field '.$c_name.' contains an invalid email address!';
                }
                break;
            }
          # check maximal length
            if (!empty($c_element->attr['maxlength']) && strlen($c_post_value) > $c_element->attr['maxlength']) {
              $c_maxlength = $c_element->attr['maxlength'];
              $this->errors[$c_name][] = 'Field '.$c_name.' contain too much characters! Maximum '.$c_maxlength.' characters.';
            }
          # check minimal length
            if (!empty($c_element->attr['minlength']) && strlen($c_post_value) < $c_element->attr['minlength']) {
              $c_minlength = $c_element->attr['minlength'];
              $this->errors[$c_name][] = 'Field '.$c_name.' contain too few characters! Minimum '.$c_minlength.' characters.';
            }
          } else if (!empty($c_element->attr['required'])) {
          # check required fields
            $this->errors[$c_name][] = 'Field '.$c_name.' can not be blank!';
          }
        }
      }
    # call custom validate
      $this->on_validate($_POST);
    # call custom submit
      if ($this->errors == []) {
        $this->on_submit($_POST);
      }
    }
  # show errors
    foreach ($this->errors as $c_el_name => $c_errors) {
    # add error messages to page
      foreach ($c_errors as $c_error) {
        message::set($c_error, 'error');
      }
    # add class 'error' to each field with error
      foreach ($elements as $c_element) {
        if (!empty($c_element->attr['name']) &&
                   $c_element->attr['name'] == $c_el_name) {
          $c_element->add_attr('class', ['error']);
          break;
        }
      }
    }
  # render form
    return parent::render();
  }

# static declarations

  static function build($form_id) {
    foreach (settings::$data['forms'] as $c_forms) {
      foreach ($c_forms as $c_form) {
        if ($c_form->id == $form_id) {
          $content = [];
          foreach ($c_form->content as $c_field) {
            $content[] = new html($c_field->type, (array)$c_field->properties);
          }
          return new html('form', (array)$c_form->properties, $content);
        }
      } 
    }
  }

}}