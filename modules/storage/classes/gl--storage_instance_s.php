<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          class storage_instance_s {

  static function parse($data) {
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

  function select($name = '') {
    $files = files::get_all(dir_modules, '%^.*\._s$%') +
             files::get_all(dir_settings, '%^.*\._s$%');
    $modules_path = [];
    foreach ($files as $c_file) {
      if ($c_file->get_file_full() == 'module._s') {
        $modules_path[$c_file->get_dir_parent()] = $c_file->get_dirs_relative();
      }
    }
    foreach ($files as $c_file) {
      $c_scope = 'global';
      foreach ($modules_path as $c_dir_parent => $c_path_relative) {
        if (strpos($c_file->get_dirs_relative(), $c_path_relative) === 0) {
          $c_scope = $c_dir_parent;
          break;
        }
      }
      $c_parsed = static::parse($c_file->load());
      foreach ($c_parsed as $c_type => $c_data) {
        if (is_object($c_data)) {
          if ($c_type == 'module') {

          }
        }
      }
      
    }
  }


}}