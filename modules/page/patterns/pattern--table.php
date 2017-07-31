<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class table extends node {

  public $template = 'table';

  function __construct($attributes = [], $body = [], $head = [], $weight = 0) {
    parent::__construct($attributes, null, $weight);
    $this->child_insert(new table_head(null, $head), 'head');
    $this->child_insert(new table_body(null, $body), 'body');
  }

  function render() {
    return (new template($this->template, [
      'attributes' => factory::data_to_attr($this->attribute_select()),
      'head'       => isset($this->children['head']) ? $this->children['head']->render() : '',
      'body'       => isset($this->children['body']) ? $this->children['body']->render() : '',
    ]))->render();
  }

}}