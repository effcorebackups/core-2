<?php

namespace effectivecore {
          class file {

  public $file;
  public $dirs;
  public $original;
  public $content;

  function __construct($path) {
    files::parse_path($path, $this);
  }

  function load() {
    $this->content = file_get_contents($this->get_path_full());
    return $this->content;
  }

  function save() {
    return file_put_contents($this->get_path_full(), $this->content);
  }

  function insert($once = true) {
    return $once ? require_once($this->get_path_full()) : 
                        require($this->get_path_full());
  }

  function is_exist() {
    return file_exists($this->get_path_full());
  }

  function get_dirs_relative() {return $this->dirs->relative;}
  function get_dirs_full() {return $this->dirs->full;}
  function get_file_full() {return $this->file->full;}
  function get_path_full() {return $this->dirs->full.'/'.$this->file->full;}
  function get_path_relative() {return $this->dirs->relative.'/'.$this->file->full;}

  function get_dir_parent() {
    return ltrim(strrchr($this->dirs->full, '/'), '/');
  }

}}