<?php

namespace effectivecore {
          class table_body_row_cell extends node {

  public $template = 'table_body_row_cell';

  function render() {
    if (count($this->children)) {
      return (new template($this->template, [
        'attributes' => $this->attributes,
        'data'       => implode("\n", $this->render_children($this->children)),
      ]))->render();
    }
  }

}}