<?php

namespace effectivecore {
          class template {

  public $name;
  public $markup;
  public $vars = [];

  function __construct($name, $vars = []) {
    $this->name = $name;
  # save vars
    foreach ($vars as $c_var_name => $c_var_value) {
      static::set_var($c_var_name, $c_var_value);
    }
  # find template
    foreach (settings_factory::$data['templates'] as $c_module_id => $c_templates) {
      foreach ($c_templates as $c_name => $c_path) {
        if ($name == $c_name) {
          $file = new file(settings_factory::$data['module'][$c_module_id]->path.'/'.$c_path);
          $this->markup = $file->load();
          return $this;
        }
      }
    }
  }

  function set_var($name, $value) {
    $this->vars[$name] = $value;
  }

  function render($clear = true) {
    $rendered = $this->markup;
  # replace variables. p.s.: '[^a-z0-9_]+|\z' - means that $c_name === '%%_$c_name' and $c_name !== '%%_$c_name_some_suffix'
    foreach ($this->vars as $c_name => $c_value) {
      $rendered = preg_replace('/%%_'.$c_name.'([^a-z0-9_]+|\z)/s', $c_value.'$1', $rendered);
    }
  # delete empty variables
    if ($clear) {
      $rendered = preg_replace('/%%_[a-z0-9_]+/s', '', $rendered);
    }
    return $rendered;
  }

}}