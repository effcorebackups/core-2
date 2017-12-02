<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          use \effectivecore\file_factory as file_factory;
          class file {

  static private $cache;

  public $file;
  public $dirs;
  public $original;
  public $data;

  function __construct($path) {
    file_factory::parse_path($path, $this);
  }

  function load($reset = false) {
    $relative = $this->get_path_relative();
    timer::tap('file load: '.$relative);
    if (!$reset && isset(static::$cache[$relative]))
           $this->data = static::$cache[$relative];
    else   $this->data = static::$cache[$relative] = file_get_contents($this->get_path_full());
    timer::tap('file load: '.$relative);
    console::add_log('file', 'load', $relative, 'ok', timer::get_period('file load: '.$relative, -1, -2));
    return $this->data;
  }

  function save() {
    return file_put_contents($this->get_path_full(), $this->data);
  }

  function insert($once = true) {
    $relative = $this->get_path_relative();
    timer::tap('file insert: '.$relative);
    $return = $once ? require_once($this->get_path_full()) :
                           require($this->get_path_full());
    timer::tap('file insert: '.$relative);
    console::add_log('file', 'insertion', $relative, 'ok', timer::get_period('file insert: '.$relative, -1, -2));
    return $return;
  }

  function get_data() {
    if (empty($this->data)) $this->load(true);
    return $this->data;
  }

  function set_data($data) {
    $this->data = $data;
  }

  function rename($new_name) {
    $path_old = $this->get_path_full();
    $path_new = $this->get_dirs_full().'/'.$new_name;
    $return = rename($path_old, $path_new);
    $this->__construct($path_new);
    return $return;
  }

  function is_exist()          {return file_exists($this->get_path_full());}

  function get_dirs_info()     {return $this->dirs;}
  function get_dirs_full()     {return $this->dirs->full;}
  function get_dirs_relative() {return $this->dirs->relative;}
  function get_file_info()     {return $this->file;}
  function get_file_full()     {return $this->file->full;}
  function get_path_full()     {return $this->dirs->full.'/'.$this->file->full;}
  function get_path_relative() {return $this->dirs->relative.'/'.$this->file->full;}
  function get_dir_parent()    {return ltrim(strrchr($this->dirs->full, '/'), '/');}
  function get_hash()          {return md5_file($this->get_path_full());}

}}