<?php

namespace effectivecore {
          class form {

  public $page_args = [];
  public $form_args = [];
  public $post_args = [];

  public $id;
  public $attributes = [];
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
        $c_name = isset($c_element->attributes->name) ? $c_element->attributes->name : '';
        $c_type = isset($c_element->attributes->type) ? $c_element->attributes->type : '';
        $c_form_value = isset($c_element->attributes->value) ? $c_element->attributes->value : '';
        $c_post_value = isset($_POST[$c_name]) ? $_POST[$c_name] : ''; # @todo: check security risks
      # define form_args and post_args
        if ($c_name) {
          $this->form_args[$c_name] = $c_form_value;
          $this->post_args[$c_name] = $c_post_value;
        }
      # check all post args
        if ($c_name &&
            $c_type != 'hidden' &&
            $c_type != 'submit') {
          $c_element->attributes->value = $c_post_value;
        # check email field
          switch ($c_type) {
            case 'email':
              if ($c_post_value && !filter_var($c_post_value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$element_id][] = 'Field '.$c_name.' contains an invalid email address!';
              }
              break;
          }
        # check required fields
          if (isset($c_element->attributes->required)) {
            if ($c_post_value == '') {
              $this->errors[$element_id][] = 'Field '.$c_name.' can not be blank!';
            }
          }
        # check max length
          if (isset($c_element->attributes->maxlength)) {
            if ($c_post_value && strlen($c_post_value) > $c_element->attributes->maxlength) {
              $this->errors[$element_id][] = 'Field '.$c_name.' contain too much symbols! Maximum '.$c_element->attributes->maxlength.' symbols.';
            }
          }
        # check min length
          if (isset($c_element->attributes->minlength)) {
            if ($c_post_value && strlen($c_post_value) < $c_element->attributes->minlength) {
              $this->errors[$element_id][] = 'Field '.$c_name.' contain too few symbols! Minimum '.$c_element->attributes->minlength.' symbols.';
            }
          }
        }
      }
    # show errors and set error class
      foreach ($this->errors as $element_id => $c_errors) {
        if (!isset($content[$element_id]->attributes->class)) $content[$element_id]->attributes->class = '';
        $content[$element_id]->attributes->class.= ' error';
        foreach ($c_errors as $c_error) {
          messages::add_new($c_error, 'error');
        }
      }
    # call submit handler
      if (count($this->errors) == 0 && isset($_POST['op'])) {
        call_user_func($this->on_submit->handler,
          $this->page_args,
          $this->form_args,
          $this->post_args
        );
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
    return (new html('form', ['id' => 'form_'.$this->id] + (array)$this->attributes, $r_content))->render();
  }

}}