<?php

namespace effectivecore {
          use \effectivecore\url_factory as urls;
          use \effectivecore\token_factory as tokens;
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\user\access_factory as access;
          class tree_item extends \effectivecore\node {

  public $title;
  public $template = 'tree_item';

  function __construct($title = '', $attributes = null, $children = null, $weight = 0) {
    parent::__construct($attributes, $children, $weight);
    $this->title = $title;
  }

  function render() {
    if (!isset($this->access) ||
        (isset($this->access) && access::check($this->access))) {
      return (new template($this->template, [
        'self'     => $this->render_self(),
        'children' => $this->render_children($this->children)
      ]))->render();
    }
  }

  function render_self() {
    $attr = clone $this->attributes;
    if (isset($attr->href)) {
      $attr->href = tokens::replace($attr->href);
      if (urls::is_active($attr->href)) {
        $attr->class[] = 'active';
      }
    }
    return (new markup('a', $attr,
      tokens::replace(translations::get($this->title))
    ))->render();
  }

}}