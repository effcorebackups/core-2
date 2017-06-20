<?php

namespace effectivecore {
          class table_body_row_cell extends node {

  public $template = 'table_body_row_cell';

  function add_child($child, $id = null) {
    return parent::add_child(
      is_string($child) || is_numeric($child) ? new text($child) : $child, $id
    );
  }

  function render() {
    if (count($this->children)) {
      return (new template($this->template, [
        'attributes' => factory::data_to_attr($this->attributes, ' '),
        'data'       => $this->render_children($this->children),
      ]))->render();
    }
  }

}}