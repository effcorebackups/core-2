<?php

namespace effectivecore {
          class html {

  public $type;            # example: a, p, b, br, div, span ...
  public $content = null;  # example: null, 'str', html object
  public $attr = [];       # example: ['selected' => false]        rendered as: <type>...</type>
                           # example: ['selected' => true]         rendered as: <type selected>...</type>
                           # example: ['selected' => 'true']       rendered as: <type selected="true">...</type>
                           # example: ['selected' => 'selected']   rendered as: <type selected="selected">...</type>
                           # example: ['class' => ['cl-1', 'cl-2'] rendered as: <type class="cl-1 cl-2">...</type>

  function __construct($type, $attr = [], $content = null) {
    $this->type = $type;
  # set attributes
    if (is_array($attr)) {
      foreach ($attr as $c_name => $c_value) {
        if ($c_value !== false) {
          $this->add_attr($c_name, $c_value);
        }
      }
    }
  # set content
    if (!is_null($content)) {
      $this->content = is_array($content) ? $content : [$content];
    }
  }

  function __get($type) {
    $return = [];
    foreach ($this->content as $id => $c_item) {
      if (isset($c_item->type) && $c_item->type == $type) {
        $return[$id] = &$c_item;
      }
    }
    return $return;
  }

  function add_attr($name, $data) {
    if (is_array($data)) {
      foreach ($data as $c_value) {
        $this->attr[$name][] = $c_value;
      }
    } else {
      $this->attr[$name] = $data;
    }
  }

  function add_element($element) {
    $this->content[] = $element;
  }

  function render() {
  # collect content
    $cont_rendered = [];
    if (is_array($this->content)) {
      foreach ($this->content as $c_element) {
        $cont_rendered[]= method_exists($c_element, 'render') ?
                          $c_element->render() :
                          $c_element;
      }
    }
  # generate output
    $template = new template(is_null($this->content) ? 'html_element_simple' : 'html_element');
    $template->set_var('type', $this->type);
    $template->set_var('attributes', implode(' ', factory::data_to_attr($this->attr)));
    $template->set_var('content', implode(nl, $cont_rendered));
    return $template->render();
  }

# static declarations

  static function to_css_class($string) {
    return str_replace(['/', ' '], '-', strtolower($string));
  }

}}