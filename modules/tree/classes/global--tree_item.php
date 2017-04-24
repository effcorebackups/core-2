<?php

namespace effectivecore {
          use \effectivecore\modules\user\access;
          class tree_item extends \effectivecore\node {

  public $title;

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

  function render() {
    if (!isset($this->access) ||
        (isset($this->access) && access::check($this->access))) {
      $rendered_children = '';
      if (count($this->children)) {
        $rendered_children = (new template('tree_item_children', [
          'children' => $this->render_children($this->children)
        ]))->render();
      }
      return (new template('tree_item', [
        'attributes' => factory::data_to_attr($this->attributes, ' '),
        'self'       => $this->render_self(),
        'children'   => $rendered_children
      ]))->render();
    }
  }

  protected function render_self() {
    $attr = clone $this->attributes;
    if (isset($attr->href)) {
      $attr->href = token_factory::replace($attr->href);
      if (urls_factory::is_active($attr->href)) {
        $attr->class = isset($attr->class) ? $attr->class.' active' : 'active';
      }
    }
    return (new template('tree_item_self', [
      'attributes' => factory::data_to_attr($attr, ' '),
      'title'      => token_factory::replace(translate_factory::t($this->title))
    ]))->render();
  }

}}