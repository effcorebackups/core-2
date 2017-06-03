<?php

namespace effectivecore {
          class table_head_row extends node {

  public $template = 'table_head_row';

  function add_child($child, $id = null) {
    if ($child instanceof table_head_row_cell) parent::add_child($child, $id);
    if ($child instanceof markup)              parent::add_child(new table_head_row_cell([], $child), $id);
    if (is_string($child))                     parent::add_child(new table_head_row_cell([], $child), $id);
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