<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          class file {

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. if the first letter in the path is '/' - it's a full path, оtherwise - relative path
  # 2. if the last letter in the path is '/' - it's a directory, оtherwise - file
  # 3. path components like '../' should be ignored!
  # 4. path components like './' should be ignored!
  # 5. windows files naming rules should be ignored!
  # ─────────────────────────────────────────────────────────────────────

  # path                      | dirs          | name    | type | relative
  # ─────────────────────────────────────────────────────────────────────
  # - .fiLe                   |               | .fiLe   |      | -
  # - .fiLe.eXt               |               | .fiLe   | eXt  | -
  # - fiLe                    |               | fiLe    |      | -
  # - fiLe.eXt                |               | fiLe    | eXt  | -
  # - fiLe.0.eXt              |               | fiLe.0  | eXt  | -
  # - diR.1/                  | diR.1/        |         |      | yes
  # - diR.1/диР.2/            | diR.1/диР.2/  |         |      | yes
  # - diR.1/диР.2/.fiLe       | diR.1/диР.2/  | .fiLe   |      | yes
  # - diR.1/диР.2/.fiLe.eXt   | diR.1/диР.2/  | .fiLe   | eXt  | yes
  # - diR.1/диР.2/fiLe        | diR.1/диР.2/  | fiLe    |      | yes
  # - diR.1/диР.2/fiLe.eXt    | diR.1/диР.2/  | fiLe    | eXt  | yes
  # - diR.1/диР.2/fiLe.0.eXt  | diR.1/диР.2/  | fiLe.0  | eXt  | yes
  # - /diR.1/                 | /diR.1/       |         |      | no
  # - /diR.1/диР.2/           | /diR.1/диР.2/ |         |      | no
  # - /diR.1/диР.2/.fiLe      | /diR.1/диР.2/ | .fiLe   |      | no
  # - /diR.1/диР.2/.fiLe.eXt  | /diR.1/диР.2/ | .fiLe   | eXt  | no
  # - /diR.1/диР.2/fiLe       | /diR.1/диР.2/ | fiLe    |      | no
  # - /diR.1/диР.2/fiLe.eXt   | /diR.1/диР.2/ | fiLe    | eXt  | no
  # - /diR.1/диР.2/fiLe.0.eXt | /diR.1/диР.2/ | fiLe.0  | eXt  | no
  # ─────────────────────────────────────────────────────────────────────
  # - c:\dir1                 | should be ignored
  # - \dir1                   | should be ignored
  # - dir1\                   | should be ignored
  # - ./dir1                  | should be ignored
  # - ../dir1/                | should be ignored
  # - /dir1/../dir3/          | should be ignored
  # - dir1                    | should be ignored (interpret as: file1)
  # - dir1/dir2               | should be ignored (interpret as: dir1/file2)
  # ─────────────────────────────────────────────────────────────────────

  static private $cache;

  public $dirs;
  public $name;
  public $type;
  public $data;

  function __construct($path) {
    $this->parse($path);
  }

  function parse($path) {
    $matches = [];
    preg_match('%^(?<dirs>.*/|)'.
                 '(?<name>.+?|)'.
                 '(?<type>[.][^.]+|)$%S', $path, $matches);
    $this->dirs = isset($matches['dirs']) ? $matches['dirs'] : '';
    $this->name = isset($matches['name']) ? $matches['name'] : '';
    $this->type = isset($matches['type']) ? ltrim($matches['type'], '.') : '';
  }

  function get_dirs() {return $this->dirs;}
  function get_name() {return $this->name;}
  function get_type() {return $this->type;}

  function get_path_relative() {return $this->get_dirs_relative().$this->get_file_full();}
  function get_dirs_relative() {return isset($this->dirs[0]) && $this->dirs[0] == '/' ? substr($this->dirs, strlen(dir_root)) : $this->dirs;}
  function get_path_full()     {return $this->type ? $this->dirs.$this->name.'.'.$this->type : $this->dirs.$this->name;}
  function get_file_full()     {return $this->type ? $this->name.'.'.$this->type : $this->name;}
  function get_name_parent()   {return ltrim(strrchr(rtrim($this->dirs, '/'), '/'), '/');}
  function get_hash()          {return md5_file($this->get_path_full());}

  function is_exist() {return file_exists($this->get_path_full());}

  function get_data() {
    if (empty($this->data)) $this->load(true);
    return $this->data;
  }

  function set_data($data) {
    $this->data = $data;
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

  function move($new_dirs, $new_name = null) {
    $path_old = $this->get_path_full();
    $path_new = $new_dirs.($new_name ?: $this->get_name());
    if (rename($path_old, $path_new)) {
      $this->__construct($path_new);
      return true;
    }
  }

  function move_uploaded($new_dirs, $new_name = null) {
    $path_old = $this->get_path_full();
    $path_new = $new_dirs.($new_name ?: $this->get_name());
    if (move_uploaded_file($path_old, $path_new)) {
      $this->__construct($path_new);
      return true;
    }
  }

  function rename($new_name) {
    $path_old = $this->get_path_full();
    $path_new = $this->get_dirs().$new_name;
    if (rename($path_old, $path_new)) {
      $this->__construct($path_new);
      return true;
    }
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

  ######################
  ### static methods ###
  ######################

  static function get_all($parent_dir, $filter = '') {
    $files = [];
    foreach (scandir($parent_dir) as $c_name) {
      if ($c_name != '.' && $c_name != '..') {
        if (is_file($parent_dir.$c_name)) {
          if (!$filter || ($filter && preg_match($filter, $parent_dir.$c_name))) {
            $files[$parent_dir.$c_name] = new static($parent_dir.$c_name);
          }
        } elseif (is_dir($parent_dir.$c_name)) {
          $files += static::get_all($parent_dir.$c_name.'/', $filter);
        }
      }
    }
    return $files;
  }

  static function name_make_safe($file_name) {
    $return = preg_replace_callback('%(?<char>[^a-z0-9_.\-])%uiS', function($m) {
      if ($m['char'] == ' ') return '-';
      if (strlen($m['char']) == 1) return dechex(ord($m['char'][0]));
      if (strlen($m['char']) == 2) return dechex(ord($m['char'][0])).dechex(ord($m['char'][1]));
      if (strlen($m['char']) == 3) return dechex(ord($m['char'][0])).dechex(ord($m['char'][1])).dechex(ord($m['char'][2]));
      return '-';
    }, $file_name);
    return substr($return, strlen($return) - 255);
  }

}}