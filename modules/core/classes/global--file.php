<?php

namespace effectivecore {
          class file {

  static function parse($path_full, &$obj = null) {
    if ($path_full[0] != '/') {
      $path_full = dir_root.'/'.$path_full;
    }
    $path_parts = explode('/', ltrim($path_full, '/'));
    if (!$obj) $obj = new \stdClass();
    $obj->path_full = $path_full;
    $obj->name_full = array_pop($path_parts);
    $obj->path = '/'.implode('/', $path_parts);
    $obj->name = substr($obj->name_full, 0, -strlen(strrchr($obj->name_full, '.')));
    $obj->type = ltrim(strrchr($obj->name_full, '.'), '.');
    $obj->parent_dir = end($path_parts);
    $obj->path_relative_full = substr($obj->path_full, strlen(dir_root));
    $obj->path_relative = substr($obj->path, strlen(dir_root) + 1);
    return $obj;
  }

  static function get_all($parent_dir, $filter = '') {
    $files = [];
    $parent_dir = rtrim($parent_dir, '/');
    foreach (scandir($parent_dir) as $c_name) {
      if ($c_name != '.' && $c_name != '..') {
        if (is_file($parent_dir.'/'.$c_name)) {
          if (!$filter || ($filter && preg_match($filter, $parent_dir.'/'.$c_name))) {
            $files[$parent_dir.'/'.$c_name] = new static($parent_dir.'/'.$c_name);
          }
        } elseif (is_dir($parent_dir.'/'.$c_name)) {
          $files += static::get_all($parent_dir.'/'.$c_name, $filter);
        }
      }
    }
    return $files;
  }

# non static declarations

  public $path_full;          # example: '/dir1/dir2/.../home/sub_dir_1/sub_dir_2/file.ext'
  public $path;               # example: '/dir1/dir2/.../home/sub_dir_1/sub_dir_2'
  public $path_relative_full; # example: 'home/sub_dir_1/sub_dir_2/file.ext'
  public $path_relative;      # example: 'home/sub_dir_1/sub_dir_2'
  public $name_full;          # example: 'file.ext'
  public $name;               # example: 'file'
  public $type;               # example: 'ext'
  public $parent_dir;         # example: 'sub_dir_2'
  public $content;

  function __construct($path_full) {
    static::parse($path_full, $this);
  }

  function load() {
    $this->content = file_get_contents($this->path_full);
    return $this->content;
  }

  function save() {
    return file_put_contents($this->path_full, $this->content);
  }

  function insert($once = true) {
    return $once ? require_once($this->path_full) : 
                        require($this->path_full);
  }

  function is_exist() {
    return file_exists($this->path_full);
  }

}}