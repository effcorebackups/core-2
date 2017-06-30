<?php

namespace effectivecore {
          class node {

  public $attributes;
  public $weight;
  public $children;
  public $template;

  function __construct($attributes = null, $children = null, $weight = 0) {
    $this->attributes = $attributes;
    $this->weight = $weight;
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

  function child_select($id) {return $this->children[$id];}
  function child_delete($id) {unset($this->children[$id]);}
  function child_change($id, $new_child) {$this->children[$id] = $new_child;}
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

  function attribute_select($key)         {return $this->attributes[$key];}
  function attribute_insert($key, $value) {$this->attributes->{$key} = $value;}

  ##############
  ### render ###
  ##############

  function render() {
    if ($this->template) {
      return (new template($this->template, [
        'attributes' => factory::data_to_attr($this->attributes, ' '),
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