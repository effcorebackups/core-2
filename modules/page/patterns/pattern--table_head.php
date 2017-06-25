<?php

namespace effectivecore {
          class table_head extends node {

  public $template = 'table_head';

  function child_insert($child, $id = null) {
    if ($child instanceof table_body_row)  return parent::child_insert($child, $id);
    if ($child instanceof entity_instance) return parent::child_insert(new table_head_row([], $child->get_values()), $id);
    if (is_array($child))                  return parent::child_insert(new table_head_row([], $child), $id);
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