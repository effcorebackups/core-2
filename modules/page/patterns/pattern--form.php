<?php

namespace effectivecore {
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\page\page_factory as pages;
          use \effectivecore\modules\storage\storage_factory as storages;
          class form extends markup {

  public $on_init             = null;
  public $on_validate         = null;
  public $on_submit           = null;
  public $clicked_button      = null;
  public $clicked_button_name = null;
  public $errors = [];

  function __construct($attributes = null, $children = null, $weight = 0) {
    parent::__construct('form', $attributes, $children, $weight);
  }

  function render() {
    $this->build();
    return parent::render();
  }

  function add_error($element_id, $data) {
    $this->errors[$element_id][] = $data;
  }

  function build() {
    $elements = factory::collect_children($this->children);
  # call init handlers
    events::start('on_form_init', $this->attributes->id, [$this, $elements]);
  # if current user click the button
    if (isset($_POST['form_id']) &&
              $_POST['form_id'] === $this->attributes->id && isset($_POST['button'])) {
    # get more info about clicked button
      foreach ($elements as $c_element) {
        if (isset($c_element->attributes->type) &&
                  $c_element->attributes->type == 'submit' &&
                  $c_element->attributes->value == $_POST['button']) {
          $this->clicked_button      = $c_element;
          $this->clicked_button_name = $c_element->attributes->value;
          break;
        }
      }
    # call validate handlers
      if (empty($this->clicked_button->novalidate)) {
        events::start('on_form_validate', $this->attributes->id, [$this, $elements]);
      }
    # show errors and set error class
      foreach ($this->errors as $c_id => $c_errors) {
        foreach ($c_errors as $c_error) {
          if (!isset($elements[$c_id]->attributes->class)) $elements[$c_id]->attributes->class = '';
          $elements[$c_id]->attributes->class.= ' error';
          messages::add_new($c_error, 'error');
        }
      }
    # call submit handler (if no errors)
      if (count($this->errors) == 0) {
        events::start('on_form_submit', $this->attributes->id, [$this, $elements]);
      }
    }

  # add form_id to the form markup
    $this->children['hidden_form_id'] = new markup('input', [
      'type'  => 'hidden',
      'name'  => 'form_id',
      'value' => $this->attributes->id,
    ]);
  }

}}