<?php

namespace effectivecore {
          class template {

  public $name;
  public $markup;
  public $vars = [];

  function __construct($tpl_name, $vars = []) {
    $this->name = $tpl_name;
  # save vars
    foreach ($vars as $c_var_name => $c_var_value) {
      static::set_var($c_var_name, $c_var_value);
    }
  # find template
    foreach (settings::$data['templates'] as $c_module_id => $c_templates) {
      foreach ($c_templates as $c_tpl_name => $c_tpl_path) {
        if ($tpl_name == $c_tpl_name) {
          $file = new file(settings::$data['module'][$c_module_id]->path.'/'.$c_tpl_path);
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