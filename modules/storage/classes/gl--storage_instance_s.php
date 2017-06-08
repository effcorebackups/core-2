<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          use \effectivecore\message_factory as messages;
          class storage_instance_s {

  static $data_original;
  static $data;

  static function init() {
    $file = new file(dir_dynamic.'cache--settings.php');
    if ($file->is_exist()) {
      $file->insert();
    } else {
      static::$data = static::settings_get_all();
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

  function insert_changes() {
    if (!static::$data) static::init();
  # ...
  }

  function update_changes() {
    if (!static::$data) static::init();
  # ...
  }

  function delete_changes() {
    if (!static::$data) static::init();
  # ...
  }

  function changes_rebuild() {
    foreach (static::changes_get_all() as $changes_by_action) {
      foreach ($changes_by_action as $c_change) {
        $path_parts = explode('/', $c_change->npath);
        $child_name = array_pop($path_parts);
        $source_obj = static::$data; # pseudo clone
        $parent_obj = &factory::npath_get_pointer(implode('/', $path_parts), $source_obj);
        switch ($c_change->action) {
          case 'insert':
            switch (gettype($parent_obj)) {
              case 'array' : $destination_obj = &$parent_obj[$child_name];   break;
              case 'object': $destination_obj = &$parent_obj->{$child_name}; break;
            }
            switch (gettype($destination_obj)) {
              case 'array' : foreach ($c_change->value as $key => $value) $destination_obj[$key]   = $value; break;
              case 'object': foreach ($c_change->value as $key => $value) $destination_obj->{$key} = $value; break;
            }
            break;
          case 'update':
            switch (gettype($parent_obj)) {
              case 'array' : $parent_obj[$child_name]   = $c_change->value; break;
              case 'object': $parent_obj->{$child_name} = $c_change->value; break;
            }
            break;
          case 'delete':
            switch (gettype($parent_obj)) {
              case 'array' : unset($parent_obj[$child_name]);   break;
              case 'object': unset($parent_obj->{$child_name}); break;
            }
            break;
        }
      }
    }
  }

  static function changes_get_all() {
    $return = [];
    $files = files::get_all(dir_dynamic, '%^.*\/changes(--.+|)\._s$%');
    foreach ($files as $c_file) {
      $c_parsed = static::_parse($c_file->load());
      if (!empty($c_parsed->changes)) {
        foreach ($c_parsed->changes as $c_id => $c_change) {
          $return[$c_change->action][$c_id] = $c_change;
        }
      }
    }
    return $return;
  }

  static function settings_get_all() {
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
      $c_parsed = static::_parse($c_file->load());
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

  static function _parse($data) {
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