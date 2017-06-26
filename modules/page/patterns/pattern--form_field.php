<?php

namespace effectivecore {
          class form_field extends node {

  public $template = 'form_field';
  public $wr_tag_name = 'x-field';
  public $wr_attributes = [];
  public $title;
  public $description;

  function render() {
    $is_has_box = $this->tag_name == 'input' && (
                  $this->attributes->type == 'checkbox' ||
                  $this->attributes->type == 'radio') ? true : false;
    if ($is_has_box) {
      $this->wr_attributes['class'] = 'has-box';
    }
    return (new template($this->template, [
      'wr_tag_name'   => $this->wr_tag_name,
      'wr_attributes' => factory::data_to_attr($this->wr_attributes, ' '),
      'attributes'    => factory::data_to_attr($this->attributes, ' '),
      'title_t'       => $is_has_box != true ? (new markup('label', [], $this->title))->render() : '',
      'title_b'       => $is_has_box == true ? (new markup('label', [], $this->title))->render() : '',
      'description'   => $this->description ? (new markup('x-description', [], $this->description))->render() : '',
      'children'      => (new markup($this->tag_name, $this->attributes, $this->children))->render()
    ]))->render();
  }

}}