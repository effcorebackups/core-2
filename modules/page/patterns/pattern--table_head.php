<?php

namespace effectivecore {
          class table_head extends node {

  public $template = 'table_head';

  function add_child($child, $id = null) {
    if ($child instanceof table_body_row)  parent::add_child($child, $id);
    if ($child instanceof entity_instance) parent::add_child(new table_head_row([], $child->get_values()), $id);
    if (is_array($child))                  parent::add_child(new table_head_row([], $child), $id);
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