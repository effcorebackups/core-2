<?php

namespace effectivecore {
          use \effectivecore\message_factory as messages;
          class form extends node {

  # support FORM elements:
  # ─────────────────────────────────────────────────────────────────────
  # html4 elements:
  # - <input type="text">
  # - <input type="password">
  # - <input type="file">
  # - <input type="checkbox">
  # - <input type="radio">
  # - <input type="hidden">
  # - <select></select>
  # - <textarea></textarea>
  # - <button type="button">
  # - <button type="reset">
  # - <button type="submit">
  #
  # html5 elements (restricted support on the browser side):
  # - <input type="search">
  # - <input type="email">
  # - <input type="url">
  # - <input type="tel">
  # - <input type="number">
  # - <input type="range">
  # - <input type="date">  p.s. you will get а warning on html validation
  # - <input type="time">  p.s. you will get а warning on html validation
  # - <input type="color"> p.s. you will get а warning on html validation
  #
  # not supported types (in this project):
  # - <input type="submit">
  # - <input type="reset">
  # - <input type="image">
  # - <input type="button">
  # ─────────────────────────────────────────────────────────────────────

  public $template = 'form';
  public $on_init;
  public $on_validate;
  public $on_submit;
  public $clicked_button;
  public $clicked_button_name;
  public $errors = [];

  function render() {
    $this->build();
    return parent::render();
  }

  function add_error($element_id, $data) {
    $this->errors[$element_id][] = $data;
  }

  function build() {
    $id = $this->attribute_select('id');
  # call init handlers
    $elements = static::collect_elements($this->children);
    events::start('on_form_init', $id, [$this, $elements]);
    $elements = static::collect_elements($this->children);
  # if current user click the button
    if (isset($_POST['form_id']) &&
              $_POST['form_id'] === $id && isset($_POST['button'])) {
    # get more info about clicked button
      foreach ($elements as $c_element) {
        if ($c_element instanceof markup &&
            $c_element->tag_name == 'button' &&
            $c_element->attribute_select('type') == 'submit' &&
            $c_element->attribute_select('value') === $_POST['button']) {
          $this->clicked_button      = $c_element;
          $this->clicked_button_name = $c_element->attribute_select('value');
          break;
        }
      }
    # call validate handlers
      if (empty($this->clicked_button->novalidate)) {
        events::start('on_form_validate', $id, [$this, $elements, $_POST]);
      }
    # show errors and set error class
      foreach ($this->errors as $c_id => $c_errors) {
        foreach ($c_errors as $c_error) {
          $elements[$c_id]->attributes['class']['error'] = 'error'; # @todo: use attribute_insert
          messages::add_new($c_error, 'error');
        }
      }
    # call submit handler (if no errors)
      if (count($this->errors) == 0) {
        events::start('on_form_submit', $id, [$this, $elements, $_POST]);
      }
    }

  # add form_id to the form markup
    $this->children['hidden_form_id'] = new markup('input', [
      'type'  => 'hidden',
      'name'  => 'form_id',
      'value' => $id,
    ]);
  }

  static function collect_elements($data, $npath = '') {
    $return = [];
    foreach ($data as $c_id => $c_item) {
      $c_npath = ltrim($npath.'/'.$c_id, '/');
      $return[$c_npath] = $c_item;
      if (isset($c_item->children)) {
        $return += static::collect_elements($c_item->children, $c_npath);
      }
    }
    return $return;
  }

}}