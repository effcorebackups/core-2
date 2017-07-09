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
  static $changes_dynamic;

  static function init() {
    $f_settings = new file(dir_dynamic.settings_cache_file_name);
    if ($f_settings->is_exist()) {
      $f_settings->insert();
    } else {
      static::settings_rebuild();
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

  function changes_register_action($module_id, $action, $npath, $value = null) {
    $f_settings      = new file(dir_dynamic.settings_cache_file_name);
    $f_settings_orig = new file(dir_dynamic.settings_cache_file_name_orig);
    $f_changes       = new file(dir_dynamic.changes_file_name);
    if ($f_changes->is_exist()) $f_changes->insert();
  # init changes
    $settings_d = isset(static::$changes_dynamic['changes']) ?
                        static::$changes_dynamic['changes'] : [];
  # add new action
    $settings_d[$module_id][$action][$npath] = $value;
  # save data
    if (!is_writable(dir_dynamic) ||
        ($f_settings_orig->is_exist() &&
        !$f_settings_orig->is_writable()) ||
        ($f_settings->is_exist() &&
        !$f_settings->is_writable()) ||
        ($f_changes->is_exist() &&
        !$f_changes->is_writable())) {
      messages::add_new(
        'Can not save file "'.changes_file_name.            '" to the directory "dynamic"!'.br.
        'Check if file "'.    changes_file_name.            '" is writable.'.br.
        'Check if file "'.    settings_cache_file_name.     '" is writable.'.br.
        'Check if file "'.    settings_cache_file_name_orig.'" is writable.'.br.
        'Check if directory "dynamic" is writable.'.br.
        'Setting is not saved.', 'error'
      );
    } else {
      static::$changes_dynamic['changes'] = $settings_d; # prevent opcache work
      static::settings_save_to_file($settings_d, changes_file_name, '  settings::$changes_dynamic[\'changes\']');
      static::settings_rebuild();
    }
  }

  ################
  ### settings ###
  ################

  static function settings_rebuild() {
    $f_settings      = new file(dir_dynamic.settings_cache_file_name);
    $f_settings_orig = new file(dir_dynamic.settings_cache_file_name_orig);
    $f_changes       = new file(dir_dynamic.changes_file_name);
    if ($f_changes->is_exist())       $f_changes->insert();
    if ($f_settings_orig->is_exist()) $f_settings_orig->insert();
  # init changes
    $settings_d = isset(static::$changes_dynamic['changes']) ?
                        static::$changes_dynamic['changes'] : [];
    $settings_s = isset(static::$data_orig['changes']) ?
                        static::$data_orig['changes'] : [];
  # init original settings
    if (empty(static::$data_orig)) {
      static::$data_orig = ['_created' => date(format_datetime, time())];
      static::$data_orig += static::settings_find_static();
    }
  # apply all changes to original settings and get final settings
    $data_new = unserialize(serialize(static::$data_orig)); # deep array clone
    static::changes_apply_to_settings($settings_d, $data_new);
    static::changes_apply_to_settings($settings_s, $data_new);
    static::$data = $data_new; # prevent opcache work
    unset(static::$data['changes']);
  # save cache
    if (!is_writable(dir_dynamic) ||
        ($f_settings_orig->is_exist() &&
        !$f_settings_orig->is_writable()) ||
        ($f_settings->is_exist() &&
        !$f_settings->is_writable())) {
      messages::add_new(
        'Can not save file "'.settings_cache_file_name.     '" to the directory "dynamic"!'.br.
        'Can not save file "'.settings_cache_file_name_orig.'" to the directory "dynamic"!'.br.
        'Check if file "'.    settings_cache_file_name.     '" is writable.'.br.
        'Check if file "'.    settings_cache_file_name_orig.'" is writable.'.br.
        'Check if directory "dynamic" is writable.'.br.
        'System is working slowly at now.', 'warning'
      );
    } else {
      static::settings_save_to_file(static::$data_orig, settings_cache_file_name_orig, '  settings::$data_orig');
      static::settings_save_to_file(static::$data,      settings_cache_file_name,      '  settings::$data');
    }
  }

  static function settings_save_to_file($data, $file_name, $prefix) {
    $file = new file(dir_dynamic.$file_name);
    $file->set_data(
      "<?php \n\nnamespace effectivecore { # ARRAY[type][scope]...\n\n  ".
        "use \\effectivecore\\storage_instance_s as settings;\n\n".
          factory::data_export($data, $prefix).
      "\n}");
    $file->save();
    opcache_invalidate($file->get_path_full());
  }

  static function settings_find_static() {
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

  ###############
  ### changes ###
  ###############

  static function changes_apply_to_settings($changes, &$data) {
    foreach ($changes as $module_id => $c_module_changes) {
      foreach ($c_module_changes as $c_action_id => $c_changes) {
        foreach ($c_changes as $c_npath => $c_value) {
          $path_parts = explode('/', $c_npath);
          $child_name = array_pop($path_parts);
          $parent_obj = &factory::npath_get_pointer(implode('/', $path_parts), $data, true);
          switch ($c_action_id) {
            case 'insert': # only structured types support (array|object)
              switch (gettype($parent_obj)) {
                case 'array' : $destination_obj = &$parent_obj[$child_name];   break;
                case 'object': $destination_obj = &$parent_obj->{$child_name}; break;
              }
              switch (gettype($destination_obj)) {
                case 'array' : foreach ($c_value as $key => $value) $destination_obj[$key]   = $value; break;
                case 'object': foreach ($c_value as $key => $value) $destination_obj->{$key} = $value; break;
              }
              break;
            case 'update': # only scalar types support (string|numeric) @todo: test bool|null
              switch (gettype($parent_obj)) {
                case 'array' : $parent_obj[$child_name]   = $c_value; break;
                case 'object': $parent_obj->{$child_name} = $c_value; break;
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
    # skip comments
      if (substr(ltrim($c_line, ' '), 0, 1) === '#') continue;
    # p.s. performance ~ 1'000'000 strings per second.
      $matches = [];
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