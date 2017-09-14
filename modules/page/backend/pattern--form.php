<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\events_factory as events;
          use \effectivecore\messages_factory as messages;
          class form extends \effectivecore\node {

  # elements support:
  # ─────────────────────────────────────────────────────────────────────
  # - textarea
  # - input[type=text]
  # - input[type=password]
  # - input[type=search]
  # - input[type=url]
  # - input[type=tel]
  # - input[type=email]
  # - select
  # - input[type=file]
  # - input[type=checkbox]
  # - input[type=radio]
  # - input[type=number]
  # - input[type=range]
  # - input[type=date]
  # - input[type=time]
  # - input[type=color]
  # - button[type=button]
  # - button[type=reset]
  # - button[type=submit]
  # - input[type=hidden]         : not processed
  # ─────────────────────────────────────────────────────────────────────

  # elements are not supported and not processed:
  # ─────────────────────────────────────────────────────────────────────
  # - input[type=button]         : use button[type=button] instead
  # - input[type=reset]          : use button[type=reset] instead
  # - input[type=submit]         : use button[type=submit] instead
  # - input[type=image]          : use imgage instead
  # - input[type=week]           : use week_macro instead
  # - input[type=month]          : use month_macro instead
  # - input[type=datetime]       : use date + time instead
  # - input[type=datetime-local] : use date + time instead
  # ─────────────────────────────────────────────────────────────────────

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. more info in \effectivecore\events_form
  # ─────────────────────────────────────────────────────────────────────

  public $template = 'form';
  public $clicked_button = null;
  public $clicked_button_name = null;
  public $errors = [];

  function render() {
    $this->build();
    return parent::render();
  }

  function add_error($element_id, $data) {
    $this->errors[$element_id][] = $data;
  }

  function build() {
    $values = $_POST;
    $id = $this->attribute_select('id');
  # build form elements
    $elements = $this->child_select_all();
    foreach ($elements as $c_element) {
      if (method_exists($c_element, 'build')) {
        $c_element->build();
      }
    }
  # call init handlers
    events::start('on_form_init', $id, [$this, $elements]);
    $elements = $this->child_select_all();
  # if current user click the button
    if (isset($values['form_id']) &&
              $values['form_id'] === $id && isset($values['button'])) {
    # get more info about clicked button
      foreach ($elements as $c_element) {
        if ($c_element instanceof markup &&
            $c_element->tag_name == 'button' &&
            $c_element->attribute_select('type') == 'submit' &&
            $c_element->attribute_select('value') === $values['button']) {
          $this->clicked_button      = $c_element;
          $this->clicked_button_name = $c_element->attribute_select('value');
          break;
        }
      }
    # call validate handlers
      if (empty($this->clicked_button->novalidate)) {
        events::start('on_form_validate', $id, [$this, $elements, &$values]);
      }
    # show errors and set error class
      foreach ($this->errors as $c_id => $c_errors) {
        foreach ($c_errors as $c_error) {
          $elements[$c_id]->attribute_insert('class', ['error' => 'error']);
          messages::add_new($c_error, 'error');
        }
      }
    # call submit handler (if no errors)
      if (count($this->errors) == 0) {
        events::start('on_form_submit', $id, [$this, $elements, &$values]);
      }
    }

  # add form_id to the form markup
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'form_id',
      'value' => $id,
    ]), 'hidden_form_id');
  }

}}