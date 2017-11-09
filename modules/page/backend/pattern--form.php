<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\events_factory as events;
          use \effectivecore\message_factory as message;
          class form extends \effectivecore\markup {

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

  public $tag_name = 'form';
  public $clicked_button = null;
  public $clicked_button_name = null;
  public $errors = [];

  function render() {
    $this->build();
    return parent::render();
  }

  function add_error($element_id = null, $message = null) {
    $this->errors[$element_id][] = $message;
  }

  function build() {
    $values = $_POST;
    $id = $this->attribute_select('id');
  # build all form elements
    $elements = $this->child_select_all();
    foreach ($elements as $c_element) {
      if (method_exists($c_element, 'build')) {
        $c_element->build();
      }
    }
  # renew elements list after build and get all fields
    $elements = $this->child_select_all();
    $fields   = $this->fields_get();
  # call init handlers
    events::start('on_form_init', $id, [$this, $fields]);
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
        events::start('on_form_validate', $id, [$this, $fields, &$values]);
      }
    # show errors and set error class
      foreach ($this->errors as $c_npath => $c_errors) {
        foreach ($c_errors as $c_error) {
          if ($c_npath) $elements[$c_npath]->attribute_insert('class', ['error' => 'error']);
          if ($c_error) message::add_new($c_error, 'error');
        }
      }
    # call submit handler (if no errors)
      if (count($this->errors) == 0) {
        events::start('on_form_submit', $id, [$this, $fields, &$values]);
      }
    }

  # add form_id to the form markup
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'form_id',
      'value' => $id,
    ]), 'hidden_form_id');
  }

  function fields_get() {
    $return = [];
    foreach ($this->child_select_all() as $c_npath => $c_child) {
      if ($c_child instanceof \effectivecore\form_container) {
        $return[$c_npath] = $c_child;
      }
    }
    return $return;
  }

}}