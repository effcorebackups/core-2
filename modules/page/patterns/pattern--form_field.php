<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_field extends node {

  public $template = 'form_field';
  public $wr_tag_name = 'x-field';
  public $wr_attributes = [];
  public $title;
  public $description;

  function render() {
    $is_has_box = $this->tag_name == 'input' && (
                  $this->attribute_select('type') == 'checkbox' ||
                  $this->attribute_select('type') == 'radio') ? true : false;
    if ($is_has_box) {
      $this->wr_attributes['class']['has_box'] = 'has-box'; # @todo: use attribute_insert
    }
    $description = [];
    if (!empty($this->description))                   $description[] = (new markup('p', ['class' => ['default']],   is_string($this->description) ? translations::get($this->description) : $this->description))->render();
    if (!empty($this->attribute_select('minlength'))) $description[] = (new markup('p', ['class' => ['minlength']], translations::get('Field should contain minimum %%_lenght symbols.', ['lenght' => $this->attribute_select('minlength')])))->render();
    if (!empty($this->attribute_select('maxlength'))) $description[] = (new markup('p', ['class' => ['maxlength']], translations::get('Field should contain maximum %%_lenght symbols.', ['lenght' => $this->attribute_select('maxlength')])))->render();
    $is_required_mark = !empty($this->attribute_select('required')) ? new markup('b', ['class' => 'required'], '*') : '';
    return (new template($this->template, [
      'wr_tag_name'   => $this->wr_tag_name,
      'wr_attributes' => factory::data_to_attr($this->wr_attributes, ' '),
      'attributes'    => factory::data_to_attr($this->attribute_select(), ' '),
      'title_t'       => $is_has_box != true ? (new markup('label',         [], [$this->title, $is_required_mark]))->render() : '',
      'title_b'       => $is_has_box == true ? (new markup('label',         [], [$this->title]))->render()                    : '',
      'description'   => count($description) ? (new markup('x-description', [], implode($description)))->render()             : '',
      'children'      => (new markup($this->tag_name, $this->attribute_select(), $this->children))->render()
    ]))->render();
  }

}}