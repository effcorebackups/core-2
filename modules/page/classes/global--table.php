<?php

namespace effectivecore {
          class table extends node {

  public $template = 'table';
  public $template_head = 'table_head';
  public $template_head_row = 'table_head_row';
  public $template_head_row_cell = 'table_head_row_cell';
  public $template_body = 'table_body';
  public $template_body_row = 'table_body_row';
  public $template_body_row_cell = 'table_body_row_cell';

  public $head;
  public $body;

  function __construct($attributes = null, $body = [], $head = [], $weight = 0) {
    parent::__construct(null, $attributes, null, $weight);
    $this->body = $body;
    $this->head = $head;
  }

  function render() {
    $rendered = '';
    foreach ($head as $c_cell) {
      $rendered.= (new template($this->template_head_row_cell, ['cell' => $c_cell]))->render();
    }
    return $rendered;
  }

}}