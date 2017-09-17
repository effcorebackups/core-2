<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class node extends \effectivecore\node_simple {

  public $children = [];

  function __construct($attributes = [], $children = [], $weight = 0) {
    parent::__construct($attributes, $weight);
    if ($children) {
      foreach (is_array($children) ? $children : [$children] as $id => $c_child) {
        $this->child_insert($c_child, $id);
      }
    }
  }

  ################
  ### children ###
  ################

  function child_select($id) {
    return isset($this->children[$id]) ?
                 $this->children[$id] : null;
  }

  function child_select_all($children = null, $npath = '') {
    $return = [];
    foreach ($children ?: $this->children as $c_id => $c_child) {
      $c_npath = ltrim($npath.'/'.$c_id, '/');
      $return[$c_npath] = $c_child;
      if (!empty($c_child->children)) {
        $return += $this->child_select_all($c_child->children, $c_npath);
      }
    }
    return $return;
  }

  function child_delete($id) {
    unset($this->children[$id]);
  }

  function child_change($id, $new_child) {
    $this->children[$id] = $new_child;
  }

  function child_insert($child, $new_id = null) {
    $id = ($new_id !== null ?
           $new_id : count($this->children));
    $this->children[$id] = $child;
    return $id;
  }

  function child_insert_after($child, $after_id, $new_id = null) {
    $id = ($new_id !== null ?
           $new_id : count($this->children));
    $children = [];
    foreach ($this->children as $c_id => $c_child) {
      $children[$c_id] = $c_child;
      if ($c_id === $after_id) {
        $children[$id] = $child;
      }
    }
    $this->children = $children;
    return $id;
  }

  ##############
  ### render ###
  ##############

  function render() {
    if ($this->template) {
      return (new template($this->template, [
        'attributes' => factory::data_to_attr($this->attribute_select()),
        'self'       => $this->render_self(),
        'children'   => $this->render_children($this->children)
      ]))->render();
    } else {
      return $this->render_self().
             $this->render_children($this->children);
    }
  }

  function render_children($children, $join = true) {
    $rendered = [];
    foreach (factory::array_sort_by_weight($children) as $c_child) {
      $rendered[] = $this->render_child($c_child);
    }
    return $join ? implode(nl, $rendered) :
                               $rendered;
  }

  function render_child($child) {
    return method_exists($child, 'render') ? $child->render() :
                                             $child;
  }

}}