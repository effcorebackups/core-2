<?php

namespace effectivecore {
          class form_n {

  public $id;
  public $properties = [];
  public $on_submit = null;
  public $on_validate = null;
  public $content = [];
  public $errors = [];

  function render() {
  # set and validate new values after submit
    if (isset($_POST['form_id']) &&
              $_POST['form_id'] == $this->id) {
    # get all form elements as flat array
      $content = factory::collect_content($this->content);
    # check each form field
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
                $this->errors[$element_id][] = 'Field '.$c_name.' contains an invalid email address!';
              }
              break;
          }
        # check required fields
          if (isset($c_element->properties->required)) {
            if (strlen($c_post_value) == 0) {
              $this->errors[$element_id][] = 'Field '.$c_name.' can not be blank!';
            }
          }
        # check max length
          if (isset($c_element->properties->maxlength)) {
            if (strlen($c_post_value) > $c_element->properties->maxlength) {
              $this->errors[$element_id][] = 'Field '.$c_name.' contain too much symbols! Maximum '.$c_element->properties->maxlength.' symbols.';
            }
          }
        # check min length
          if (isset($c_element->properties->minlength)) {
            if (strlen($c_post_value) < $c_element->properties->minlength) {
              $this->errors[$element_id][] = 'Field '.$c_name.' contain too few symbols! Minimum '.$c_element->properties->minlength.' symbols.';
            }
          }
        }
      }
    # show errors and set error class
      foreach ($this->errors as $element_id => $c_errors) {
        if (!isset($content[$element_id]->properties->class)) $content[$element_id]->properties->class = '';
        $content[$element_id]->properties->class.= ' error';
        foreach ($c_errors as $c_error) {
          message::set($c_error, 'error');
        }
      }
    # call submit handler
      if (count($this->errors) == 0 && isset($_POST['op'])) {
        call_user_func_array($this->on_submit->handler, [$_POST]);
      }
    }
  # render form elements
    $r_content = [new html('input', ['type' => 'hidden', 'name' => 'form_id', 'value' => $this->id])];
    foreach ($this->content as $c_element) {
      $r_content[] = method_exists($c_element, 'render') ?
                                   $c_element->render() :
                                   $c_element;
    }
  # return rendered form
    return (new html('form', (array)$this->properties, $r_content))->render();
  }

}}