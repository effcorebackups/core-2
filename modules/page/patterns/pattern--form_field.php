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
      $this->wr_attributes['class'][] = 'has-box';
    }
    $description = [];
    if (!empty($this->description))           $description[] = (new markup('p', ['class' => ['default']], $this->description))->render();
    if (!empty($this->attributes->minlength)) $description[] = (new markup('p', ['class' => ['minlength']], 'Field should contain minimum '.$this->attributes->minlength.' symbols.'))->render();
    if (!empty($this->attributes->maxlength)) $description[] = (new markup('p', ['class' => ['maxlength']], 'Field should contain maximum '.$this->attributes->maxlength.' symbols.'))->render();
    $is_required_mark = !empty($this->attributes->required) ? new markup('spam', ['class' => 'required'], '*') : '';
    return (new template($this->template, [
      'wr_tag_name'   => $this->wr_tag_name,
      'wr_attributes' => factory::data_to_attr($this->wr_attributes, ' '),
      'attributes'    => factory::data_to_attr($this->attributes, ' '),
      'title_t'       => $is_has_box != true ? (new markup('label',         [], [$this->title, $is_required_mark]))->render() : '',
      'title_b'       => $is_has_box == true ? (new markup('label',         [], [$this->title]))->render()                    : '',
      'description'   => count($description) ? (new markup('x-description', [], implode($description)))->render()             : '',
      'children'      => (new markup($this->tag_name, $this->attributes, $this->children))->render()
    ]))->render();
  }

}}