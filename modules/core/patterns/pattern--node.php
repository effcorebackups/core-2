<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class node {

  public $weight = 0;
  public $attributes = [];
  public $children = [];
  public $template = null;

  function __construct($attributes = [], $children = [], $weight = 0) {
    $this->weight = $weight;
    if ($attributes) {
      foreach ($attributes as $id => $c_attribute) {
        $this->attribute_insert($id, $c_attribute);
      }
    }
    if ($children) {
      if (is_array($children)) {
        foreach ($children as $id => $c_child) {
          $this->child_insert($c_child, $id);
        }
      } else {
        $this->child_insert($children);
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

  ##################
  ### attributes ###
  ##################

  function attribute_select($key = '') {
    if ($key) {
      return isset($this->attributes[$key]) ?
                   $this->attributes[$key] : null;
    } else {
      return $this->attributes;
    }
  }

  function attribute_insert($key, $data) {
    if (is_array($data)) {
      foreach ($data as $c_key => $c_value) {
        $this->attributes[$key][$c_key] = $c_value;
      }
    } else {
      $this->attributes[$key] = $data;
    }
  }

  function attribute_delete($key) {
    unset($this->attributes[$key]);
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

  function render_self() {
    return isset($this->title) ?
                 $this->title : '';
  }

  function render_children($children, $join = true) {
    $rendered = [];
    if (is_array($children)) {
      foreach (factory::array_sort_by_weight($children) as $c_child) {
        $rendered[] = $this->render_child($c_child);
      }
    } else {
      $rendered[] = $this->render_child($children);
    }
    return $join ? implode(nl, $rendered) :
                               $rendered;
  }

  function render_child($child) {
    return method_exists($child, 'render') ? $child->render() :
                                             $child;
  }

}}