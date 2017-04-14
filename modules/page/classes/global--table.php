<?php

namespace effectivecore {
          class table extends dom_node {

  public $template = 'table';

  function __construct($attributes = null, $body = [], $head = [], $weight = 0) {
    parent::__construct($attributes, null, $weight);
    $this->add_child(new table_head(null, $head), 'head');
    $this->add_child(new table_body(null, $body), 'body');
  }

  function render() {
    return (new template($this->template, [
      'attributes' => implode(' ', factory::data_to_attr($this->attributes)),
      'head'       => $this->children['head']->render(),
      'body'       => $this->children['body']->render(),
    ]))->render();
  }

}}