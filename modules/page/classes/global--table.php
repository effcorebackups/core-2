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
    $rendered_head = '';
    $rendered_body = '';
  # render table head
    foreach ($this->head as $c_row) {
      $buf = '';
      foreach ($c_row as $c_cell) $buf.= $this->render_head_row_cell($c_cell);
      $rendered_head.= $this->render_head_row($buf);
    }
  # render table body
    foreach ($this->body as $c_row) {
      $buf = '';
      foreach ($c_row as $c_cell) $buf.= $this->render_body_row_cell($c_cell);
      $rendered_body.= $this->render_body_row($buf);
    }
  # return rendered table
    return (new template($this->template, [
      'head' => $this->render_head($rendered_head),
      'body' => $this->render_body($rendered_body),
    ]))->render();
  }

  function render_head($rows)          {return (new template($this->template_head,          ['rows' => $rows]))->render();}
  function render_head_row($cells)     {return (new template($this->template_head_row,      ['cells' => $cells]))->render();}
  function render_head_row_cell($cell) {return (new template($this->template_head_row_cell, ['cell' => $cell]))->render();}
  function render_body($rows)          {return (new template($this->template_body,          ['rows' => $rows]))->render();}
  function render_body_row($cells)     {return (new template($this->template_body_row,      ['cells' => $cells]))->render();}
  function render_body_row_cell($cell) {return (new template($this->template_body_row_cell, ['cell' => $cell]))->render();}

}}