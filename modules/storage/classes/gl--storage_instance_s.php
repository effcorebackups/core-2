<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          use \effectivecore\message_factory as messages;
          class storage_instance_s {

  static $data;

  static function init() {
    $file = new file(dir_dynamic.'cache--settings.php');
    if ($file->is_exist()) {
      $file->insert();
    } else {
      static::$data = static::load_all_and_parse();
      if (is_writable(dir_dynamic)) {
        $file->set_data(
          "<?php \n\nnamespace effectivecore { # settings::\$data[type][scope]...\n\n  ".
            "use \\effectivecore\\storage_instance_s as settings;\n\n".
             factory::data_export(static::$data, '  settings::$data').
          "\n}");
        $file->save();
      } else {
        messages::add_new(
          'Can not write "cache-settings.php" to the directory "dynamic"!'.br.
          'Directory "dynamic" should be writable.'.br.
          'System is working slowly at now.', 'warning'
        );
      }
    }
  }

  function select($group = '') {
    if (!static::$data) static::init();
    if ($group)  return static::$data[$group];
    else         return static::$data;
  }

  function get_diff($a, $b) {
  }

  static function load_all_and_parse() {
    $return = [];
    $files = files::get_all(dir_modules, '%^.*\._s$%');
    $modules_path = [];
    foreach ($files as $c_file) {
      if ($c_file->get_file_full() == 'module._s') {
        $modules_path[$c_file->get_dir_parent()] = $c_file->get_dirs_relative();
      }
    }
    foreach ($files as $c_file) {
      $c_scope = 'global';
      foreach ($modules_path as $c_dir_parent => $c_dir_relative) {
        if (strpos($c_file->get_dirs_relative(), $c_dir_relative) === 0) {
          $c_scope = $c_dir_parent;
          break;
        }
      }
      $c_parsed = static::parse_data($c_file->load());
      foreach ($c_parsed as $c_type => $c_data) {
        if (is_object($c_data)) {
          if ($c_type == 'module') $c_data->path = $modules_path[$c_scope];
          $return[$c_type][$c_scope] = $c_data;
        }
        if (is_array($c_data)) {
          if (!isset($return[$c_type][$c_scope])) $return[$c_type][$c_scope] = [];
          $return[$c_type][$c_scope] += $c_data;
        }
      }
    }
    return $return;
  }

  static function parse_data($data) {
    $return = new \StdClass();
    $p = [-1 => &$return];
    foreach (explode(nl, $data) as $c_line) {
      $matches = [];
    # p.s. performance ~ 1'000'000 strings per second.
      preg_match('%(?<indent>[ ]*)'.
                  '(?<prefix>\- |)'.
                  '(?<name>[^\:\|]+)'.
                  '(?<class>\\|[a-z0-9_\\\\]+|)'.
                  '(?<delimiter>\: |)'.
                  '(?<value>.*|)%sS', $c_line, $matches);
      if ($matches['name']) {
        $depth = strlen($matches['indent'].$matches['prefix']) / 2;
        if ($matches['delimiter'] == ': ') {
          $value = $matches['value'];
          if ((string)(int)$value === $value) $value = (int)$value;
          if ($value === 'true')  $value = true;
          if ($value === 'false') $value = false;
          if ($value === 'null')  $value = null;
        } else {
          $class = !empty($matches['class']) ? str_replace('|', '\\effectivecore\\', $matches['class']) : '\StdClass';
          $value = new $class;
        }
      # add new item to tree
        if (is_array($p[$depth-1])) {
          $p[$depth-1][$matches['name']] = $value;
          $p[$depth] = &$p[$depth-1][$matches['name']];
        } else {
          $p[$depth-1]->{$matches['name']} = $value;
          $p[$depth] = &$p[$depth-1]->{$matches['name']};
        }
      # convert parent item to array
        if ($matches['prefix'] == '- ' && !is_array($p[$depth-1])) {
          $p[$depth-1] = (array)$p[$depth-1];
        }
      }
    }
    return $return;
  }

}}