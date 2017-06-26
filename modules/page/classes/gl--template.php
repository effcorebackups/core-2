<?php

namespace effectivecore {
          use \effectivecore\modules\storage\storage_factory as storages;
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
    foreach (storages::get('settings')->select('templates') as $c_module_id => $c_templates) {
      foreach ($c_templates as $c_name => $c_path) {
        if ($name == $c_name) {
          $file = new file(storages::get('settings')->select('module')[$c_module_id]->path.'/'.$c_path);
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
    $rendered = preg_replace_callback('/(?<marker>%%_)(?<token>[a-z0-9_]+)/', function($matches){
      return isset($matches['marker']) &&
             isset($matches['token']) &&
             isset($this->vars[$matches['token']]) ?
                   $this->vars[$matches['token']] : '';
    }, $rendered);
    return $rendered;
  }

}}