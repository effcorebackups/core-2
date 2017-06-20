<?php

namespace effectivecore {
          class table_body_row extends node {

  public $template = 'table_body_row';

  function add_child($child, $id = null) {
    if ($child instanceof table_body_row_cell)   return parent::add_child($child, $id);
    if ($child instanceof markup)                return parent::add_child(new table_body_row_cell([], $child), $id);
    if (is_string($child) || is_numeric($child)) return parent::add_child(new table_body_row_cell([], $child), $id);
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