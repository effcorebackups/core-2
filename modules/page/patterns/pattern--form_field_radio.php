<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_field_radio extends form_field {

  public $template = 'form_field_radio';
  public $wr_tag_name = 'x-field';
  public $wr_attributes = [];
  public $tag_name = 'input';
  public $title;
  public $description;

  function render() {
    $this->wr_attributes['class']['has-box'] = 'has-box'; # @todo: use attribute_insert
    return (new template($this->template, [
      'wr_tag_name'   => $this->wr_tag_name,
      'wr_attributes' => factory::data_to_attr($this->attribute_select('', 'wr_attributes'), ' '),
      'attributes'    => factory::data_to_attr($this->attribute_select(), ' '),
      'title'         => (new markup('label', [], [$this->title, $this->_render_required_mark()]))->render(),
      'children'      => (new markup($this->tag_name, $this->attribute_select(), $this->children))->render(),
      'description'   => $this->_render_description()
    ]))->render();
  }

}}