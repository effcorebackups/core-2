<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          use \effectivecore\message_factory as messages;
          const settings_cache_file_name      = 'cache--settings.php';
          const settings_cache_file_name_orig = 'cache--settings--original.php';
          const changes_file_name             = 'changes.php';
          class storage_instance_s {

  static $data_orig;
  static $data;

  static function init() {
    $s_file      = new file(dir_dynamic.settings_cache_file_name);
    $s_file_orig = new file(dir_dynamic.settings_cache_file_name_orig);
    if ($s_file->is_exist()) {
      $s_file->insert();
    } else {
      $data_orig = ['_created' => date(format_datetime, time())] + static::settings_get_all();
      $data = unserialize(serialize($data_orig)); # deep array clone
      static::changes_apply_to_settings($data['changes'], $data);
      static::changes_apply_to_settings(static::changes_get_dynamic(), $data);
      unset($data['changes']);
      static::$data = $data;
    # save cache
      if (!is_writable(dir_dynamic) ||
          ($s_file->is_exist()      && !$s_file->is_writable()) ||
          ($s_file_orig->is_exist() && !$s_file_orig->is_writable())) {
        messages::add_new(
          'Can not save data to the directory "dynamic"!'.br.
          'Directory "dynamic" and files inside should be writable.'.br.
          'System is working slowly at now.', 'warning'
        );
      } else {
        static::settings_save_to_file($data_orig, settings_cache_file_name_orig, '  settings::$data_orig');
        static::settings_save_to_file($data,      settings_cache_file_name,      '  settings::$data');
      }
    }
  }

  ########################
  ### shared functions ###
  ########################

  function select($group = '') {
    if (!static::$data) static::init();
    if ($group)  return static::$data[$group];
    else         return static::$data;
  }

  function changes_register_action($module_id, $data) {
    $changes = static::changes_get_dynamic();
    $changes[$module_id][$data->action.'_'.str_replace('/', '_', $data->npath)] = $data;
    static::settings_save_to_file($changes, changes_file_name, '  settings::$data_orig');
  # ...
  }

  ################
  ### settings ###
  ################

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
      $c_parsed = static::settings_to_code($c_file->load());
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

  static function settings_save_to_file($data, $file_name, $prefix) {
    $file = new file(dir_dynamic.$file_name);
    $file->set_data(
      "<?php \n\nnamespace effectivecore { # ARRAY[type][scope]...\n\n  ".
        "use \\effectivecore\\storage_instance_s as settings;\n\n".
          factory::data_export($data, $prefix).
      "\n}");
    return $file->save();
  }

  ###############
  ### changes ###
  ###############

  static function changes_get_static() {
    $file = new file(dir_dynamic.settings_cache_file_name_orig);
    if ($file->is_exist()) {
      $file->insert();
    }
    return isset(static::$data_orig['changes']) ?
                 static::$data_orig['changes'] : [];
  }

  static function changes_get_dynamic() {
    $file = new file(dir_dynamic.changes_file_name);
    if ($file->is_exist()) {
      $file->insert();
    }
    return isset(static::$data_orig['changes_dynamic']) ?
                 static::$data_orig['changes_dynamic'] : [];
  }

  static function changes_apply_to_settings($changes, &$data) {
    foreach ($changes as $changes_by_action) {
      foreach ($changes_by_action as $c_change) {
        $path_parts = explode('/', $c_change->npath);
        $child_name = array_pop($path_parts);
        $parent_obj = &factory::npath_get_pointer(implode('/', $path_parts), $data);
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

  ##############
  ### parser ###
  ##############

  static function code_to_settings($data, $entity_name = '', $entity_prefix = '  ', $depth = 0) {
    $return = [];
    if ($entity_name) {
      $return[] = str_repeat('  ', $depth-1).($depth ? $entity_prefix : '').$entity_name;
    }
    foreach ($data as $key => $value) {
      if (is_array($value)  && !count($value))           continue;
      if (is_object($value) && !get_object_vars($value)) continue;
      if (is_array($value))       $return[] = static::code_to_settings($value, $key, is_array($data) ? '- ' : '  ', $depth + 1);
      else if (is_object($value)) $return[] = static::code_to_settings($value, $key, is_array($data) ? '- ' : '  ', $depth + 1);
      else if ($value === null)   $return[] = str_repeat('  ', $depth).(is_array($data) ? '- ' : '  ').$key.': null';
      else if ($value === false)  $return[] = str_repeat('  ', $depth).(is_array($data) ? '- ' : '  ').$key.': false';
      else if ($value === true)   $return[] = str_repeat('  ', $depth).(is_array($data) ? '- ' : '  ').$key.': true';
      else                        $return[] = str_repeat('  ', $depth).(is_array($data) ? '- ' : '  ').$key.': '.$value;
    }
    return implode(nl, $return);
  }

  static function settings_to_code($data) {
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
          if (is_numeric($value)) $value += 0;
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