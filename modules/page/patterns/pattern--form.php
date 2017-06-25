<?php

namespace effectivecore {
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\page\page_factory as pages;
          use \effectivecore\modules\storage\storage_factory as storages;
          class form extends node {

  public $template            = 'form';
  public $on_init             = null;
  public $on_validate         = null;
  public $on_submit           = null;
  public $clicked_button      = null;
  public $clicked_button_name = null;
  public $errors = [];

  function render() {
    $this->build();
    $rendered_children = $this->render_children($this->children);
    return (new template($this->template, [
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'content'    => $rendered_children
    ]))->render();
  }

  function add_error($element_id, $data) {
    $this->errors[$element_id][] = $data;
  }

  function build() {
    $elements = static::collect_elements($this->children);
  # add labels
    foreach ($elements as $c_npath => $c_element) {
      if (!empty($c_element->title) && isset($c_element->tag_name)) {
        if ($c_element->tag_name == 'textarea' ||
            $c_element->tag_name == 'select'   ||
            $c_element->tag_name == 'input') {
          $npath_info = factory::npath_get_info($c_npath);
          $parent_obj = &factory::npath_get_pointer($npath_info->parent_npath, $this->children);
          $element_id = $npath_info->id;
          $is_has_box = $c_element->tag_name == 'input' && (
                        $c_element->attributes->type == 'checkbox' ||
                        $c_element->attributes->type == 'radio') ? true : false;
          $parent_obj->child_insert_after(
            new markup('label', $is_has_box ? ['class' => 'has-box'] : [], [
              'label'     => new text($c_element->title, $is_has_box ? 100 : 0),
              $element_id => $c_element
          ]), $element_id, $element_id.'_wrapper');
          $parent_obj->child_delete($element_id);
        }
      }
    }
  # new collect of elements
    $elements = static::collect_elements($this->children);
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