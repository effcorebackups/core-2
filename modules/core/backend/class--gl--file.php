<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\files_factory as files;
          use \effectivecore\timers_factory as timers;
          use \effectivecore\events_factory as events;
          use \effectivecore\console_factory as console;
          class file {

  static private $cache;

  public $file;
  public $dirs;
  public $original;
  public $data;

  function __construct($path) {
    files::parse_path($path, $this);
  }

  function load($reset = false, $with_events = false) {
    if ($with_events) events::start('on_file_load_before', 'all', [$this]);
    $relative = $this->get_path_relative();
    if (!$reset && isset(static::$cache[$relative]))
           $this->data = static::$cache[$relative];
    else   $this->data = static::$cache[$relative] = file_get_contents($this->get_path_full());
    if ($with_events) events::start('on_file_load_after', 'all', [$this]);
    return $this->data;
  }

  function save() {
    return file_put_contents($this->get_path_full(), $this->data);
  }

  function insert($once = true, $with_events = false) {
    if ($with_events) events::start('on_file_insert_before', 'all', [$this]);
    $relative = $this->get_path_relative();
    $return = $once ? require_once($this->get_path_full()) :
                           require($this->get_path_full());
    if ($with_events) events::start('on_file_insert_after', 'all', [$this]);
    return $return;
  }

  function get_data() {
    if (empty($this->data)) $this->load(true);
    return $this->data;
  }

  function set_data($data) {
    $this->data = $data;
  }

  function is_exist()          {return file_exists($this->get_path_full());}
  function is_writable()       {return is_writable($this->get_path_full());}

  function get_dirs_info()     {return $this->dirs;}
  function get_file_info()     {return $this->file;}
  function get_dirs_full()     {return $this->dirs->full;}
  function get_file_full()     {return $this->file->full;}
  function get_path_full()     {return $this->dirs->full.'/'.$this->file->full;}
  function get_dirs_relative() {return $this->dirs->relative;}
  function get_path_relative() {return $this->dirs->relative.'/'.$this->file->full;}
  function get_dir_parent()    {return ltrim(strrchr($this->dirs->full, '/'), '/');}
  function get_hash()          {return md5_file($this->get_path_relative());}

}}