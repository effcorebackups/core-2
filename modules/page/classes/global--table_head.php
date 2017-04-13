<?php

namespace effectivecore {
          class table_head extends node {

  public $template = 'table_head';

  function __construct($attributes = null, $children = null) {
    parent::__construct(null, $attributes);
    unset($this->title);
    foreach ($children as $c_child) {
      $this->add_child(new table_head_row([], $c_child));
    }
  }

  function render() {
    if (count($this->children)) {
      return (new template($this->template, [
        'attributes' => $this->attributes,
        'data'       => implode("\n", $this->render_children($this->children)),
      ]))->render();
    }
  }

}}