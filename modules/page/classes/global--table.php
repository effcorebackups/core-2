<?php

namespace effectivecore {
          class table extends node {

  public $template = 'table';

  function __construct($attributes = null, $body = [], $head = [], $weight = 0) {
    parent::__construct(null, $attributes, null, $weight);
    unset($this->title);
    $this->add_child(new table_head(null, $head), 'head');
    $this->add_child(new table_body(null, $body), 'body');
  }

  function render() {
    return (new template($this->template, [
      'head' => $this->children['head']->render(),
      'body' => $this->children['body']->render(),
    ]))->render();
  }

}}