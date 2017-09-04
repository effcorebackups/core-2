<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          class template {

  public $name;
  public $markup;
  public $vars = [];

  function __construct($name, $vars = []) {
    $this->name = $name;
  # save vars
    foreach ($vars as $c_name => $c_value) {
      static::set_var($c_name, $c_value);
    }
  # find template
    foreach (storages::get('settings')->select('templates') as $c_module_id => $c_templates) {
      foreach ($c_templates as $c_name => $c_path) {
        if ($name == $c_name) {
          $file = new file(storages::get('settings')->select('module')[$c_module_id]->path.'/'.$c_path);
          $this->markup = $file->load(false);
          return $this;
        }
      }
    }
  }

  function set_var($name, $value) {
    $this->vars[$name] = $value;
  }

  function render() {
    $rendered = $this->markup;
    $rendered = preg_replace_callback('%(?<spacer>[ ]*)'.
                                       '(?<prefix>\\%\\%_)'.
                                       '(?<name>[a-z0-9_]+)'.
                                       '(?<args>\\{[a-z0-9_,]+\\}|)%sS', function($matches) {
      return !empty($matches['prefix']) &&
             !empty($matches['name']) &&
             !empty($this->vars[$matches['name']]) ? $matches['spacer'].
                    $this->vars[$matches['name']] : '';
    }, $rendered);
    return $rendered;
  }

}}