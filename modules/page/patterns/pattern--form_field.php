<?php

namespace effectivecore {
          use \effectivecore\translate_factory as translations;
          class form_field extends form_container {

  public $template = 'form_field';
  public $tag_name = 'x-field';
  public $title;
  public $description;

  function render() {
    $attributes = [];
    $description = $this->description;
    $default = $this->child_select('default');
    if ($default instanceof markup) {
      if ($default->attribute_select('required')) $this->attribute_insert('required', 'required');
      if (!empty($default->description))          $description.= $default->description;
      if (!empty($default->attribute_select()))   $attributes += $default->attribute_select();
    }
    return (new template($this->template, [
      'attributes'  => factory::data_to_attr($this->attribute_select(), ' '),
      'tag_name'    => $this->tag_name,
      'title'       => $this->render_self(),
      'content'     => $this->render_children($this->children),
      'description' => $this->render_description($description, $attributes)
    ]))->render();
  }

}}