<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          use \effectivecore\timer_factory as timers;
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

  function load() {
    $relative = $this->get_path_relative();
    if (isset(static::$cache[$relative])) {
      console::add_log('Load', $relative, 'ok', '0.000000');
      return static::$cache[$relative];
    } else {
      timers::tap('load_'.$relative);
      static::$cache[$relative] = file_get_contents($this->get_path_full());
      timers::tap('load_'.$relative);
      console::add_log('Load', $relative, 'ok', timers::get_period('load_'.$relative, 0, 1));
      return static::$cache[$relative];
    }
  }

  function save() {
    return file_put_contents($this->get_path_full(), $this->data);
  }

  function insert($once = true) {
    $relative = $this->get_path_relative();
    timers::tap('insert_'.$relative);
    $return = $once ? require_once($this->get_path_full()) : require($this->get_path_full());
    timers::tap('insert_'.$relative);
    console::add_log('Insert', $relative, 'ok', timers::get_period('insert_'.$relative, 0, 1));
    return $return;
  }

  function is_exist() {
    return file_exists($this->get_path_full());
  }

  function get_dirs_info()     {return $this->dirs;}
  function get_file_info()     {return $this->file;}
  function get_dirs_full()     {return $this->dirs->full;}
  function get_file_full()     {return $this->file->full;}
  function get_dirs_relative() {return $this->dirs->relative;}
  function get_path_full()     {return $this->dirs->full.'/'.$this->file->full;}
  function get_path_relative() {return $this->dirs->relative.'/'.$this->file->full;}
  function get_dir_parent()    {return ltrim(strrchr($this->dirs->full, '/'), '/');}
  function set_data($data)     {$this->data = $data;}

}}