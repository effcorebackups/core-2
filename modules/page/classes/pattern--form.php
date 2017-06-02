<?php

namespace effectivecore {
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\page\page_factory as pages;
          class form extends markup {

  public $on_init     = null;
  public $on_validate = null;
  public $on_submit   = null;
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
    if (isset($_POST['form_id']) &&
              $_POST['form_id'] === $this->attributes->id && isset($_POST['button'])) {
      $elements = factory::collect_children($this->children);
      $c_button = null;
    # get more info about clicked button
      foreach ($elements as $c_element) {
        if (isset($c_element->attributes->type) &&
                  $c_element->attributes->type == 'submit' &&
                  $c_element->attributes->value == $_POST['button']) {
          $c_button = $c_element;
          break;
        }
      }
    # call validate handlers
      if (empty($c_button->novalidate)) {
        if (is_array($this->on_validate)) {
          foreach (factory::array_sort_by_weight($this->on_validate) as $c_validate) {
            call_user_func($c_validate->handler, $this, $elements);
          }
        }
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
        if (is_array($this->on_submit)) {
          foreach (factory::array_sort_by_weight($this->on_submit) as $c_submit) {
            call_user_func($c_submit->handler, $this, $elements);
          }
        }
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