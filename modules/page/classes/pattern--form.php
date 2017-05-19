<?php

namespace effectivecore {
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\page\page_factory as page;
          class form extends markup {

  public $form_args = [];
  public $post_args = [];
  public $on_submit = null;
  public $on_validate = null;
  public $errors = [];

  function __construct($attributes = null, $children = null, $weight = 0) {
    parent::__construct('form', $attributes, $children, $weight);
  }

  function render() {
    $this->build();
    return parent::render();
  }

  function build() {
  # set and validate new values after submit
    if (isset($_POST['form_id']) &&
              $_POST['form_id'] == $this->attributes->id) {
    # get all form elements as flat array
      $children = factory::collect_children($this->children);
    # check each form field
      foreach ($children as $element_id => $c_element) {
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
        if (!isset($children[$element_id]->attributes->class)) $children[$element_id]->attributes->class = '';
        $children[$element_id]->attributes->class.= ' error';
        foreach ($c_errors as $c_error) {
          messages::add_new($c_error, 'error');
        }
      }
    # call validate handler
      if (isset($_POST['button']) &&
          isset($this->on_validate->handler)) {
        call_user_func(
          $this->on_validate->handler, page::$args,
          $this->form_args,
          $this->post_args
        );
      }
    # call submit handler
      if (isset($_POST['button']) &&
          isset($this->on_submit->handler) &&
          count($this->errors) == 0) {
        call_user_func(
          $this->on_submit->handler, page::$args,
          $this->form_args,
          $this->post_args
        );
      }
    }
  # add form id to form markup
    $this->children['hidden_form_id'] = new markup('input', [
      'type' => 'hidden',
      'name' => 'form_id',
      'value' => $this->attributes->id,
    ]);
  }

}}