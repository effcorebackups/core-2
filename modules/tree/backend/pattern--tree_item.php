<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\tokens_factory as tokens;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\user\access_factory as access;
          class tree_item extends \effectivecore\node {

  public $id;
  public $id_parent;
  public $parent_is_tree;
  public $title = '';
  public $template = 'tree_item';
  public $template_children = 'tree_item_children';

  function __construct($title = '', $attributes = [], $children = [], $weight = 0) {
    if ($title) $this->title = $title;
    parent::__construct($attributes, $children, $weight);
  }

  function render() {
    if (!isset($this->access) ||
        (isset($this->access) && access::check($this->access))) {
      $rendered_children = count($this->children) ? (new template($this->template_children, [
        'children' => $this->render_children($this->children)]
      ))->render() : '';
      return (new template($this->template, [
        'self'     => $this->render_self(),
        'children' => $rendered_children
      ]))->render();
    }
  }

  function render_self() {
    $href = $this->attribute_select('href');
    if ($href) {
      $href = tokens::replace($href);
      $this->attribute_insert('href', $href);
      if (urls::is_active($href)) {
        $this->attribute_insert('class', ['active' => 'active']);
      }
    }
    return (new markup('a', $this->attribute_select(),
      tokens::replace(translations::get($this->title))
    ))->render();
  }

}}