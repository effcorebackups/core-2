<?php

namespace effectivecore {
          class table extends node {

  public $template = 'table';

  function __construct($attributes = null, $body = [], $head = [], $weight = 0) {
    parent::__construct($attributes, null, $weight);
    $this->child_insert(new table_head(null, $head), 'head');
    $this->child_insert(new table_body(null, $body), 'body');
  }

  function render() {
    return (new template($this->template, [
      'attributes' => factory::data_to_attr($this->attributes, ' '),
      'head'       => isset($this->children['head']) ? $this->children['head']->render() : '',
      'body'       => isset($this->children['body']) ? $this->children['body']->render() : '',
    ]))->render();
  }

}}