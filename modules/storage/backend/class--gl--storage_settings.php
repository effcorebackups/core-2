<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\cache_factory as cache;
          use \effectivecore\file_factory as file_factory;
          use \effectivecore\dynamic_factory as dynamic;
          use \effectivecore\console_factory as console;
          use \effectivecore\message_factory as message;
          class storage_settings {

  static $data_orig;
  static $data = [];
  static $changes_dynamic;

  static function init($group) {
    console::add_log('storage', 'cache', 'storage %%_id cache for group %%_group will be load', 'ok', 0, ['id' => 'settings', 'group' => $group]);
    $cache = cache::get('settings--'.$group);
    if ($cache) static::$data[$group] = $cache;
    else        static::settings_cache_rebuild();
  }

  ########################
  ### shared functions ###
  ########################

  function select_group($group) {
    if   (!isset(static::$data[$group])) static::init($group);
    return isset(static::$data[$group]) ?
                 static::$data[$group] : null;
  }

  function select_by_npath($npath) {
    $npath_parts = explode('/', $npath);
    $group = array_shift($npath_parts);
    $group_data = $this->select_group($group);
    return factory::npath_get_object(implode('/', $npath_parts), $group_data);
  }

  ################
  ### settings ###
  ################

  static function settings_cache_rebuild() {
  # init original settings
    $data_orig = cache::get('settings_original');
    if (!$data_orig) {
      static::$data_orig = $data_orig = static::settings_find_static();
      cache::set('settings_original', $data_orig, ['build' => factory::datetime_get()]);
    }
  # init dynamic and static changes
    $changes_d = dynamic::get('changes') ?: [];
    $changes_s = isset($data_orig['changes']) ? $data_orig['changes'] : [];
  # apply all changes to original settings and get final settings
    $data = factory::array_deep_clone($data_orig);
    static::changes_apply_to_settings($changes_d, $data);
    static::changes_apply_to_settings($changes_s, $data);
    unset($data['changes']);
  # save cache
    foreach ($data as $group => $slice) {
      cache::set('settings--'.$group, $slice);
      static::$data[$group] = $slice;
    }
  }

  static function settings_find_static() {
    $return = [];
    $files = file_factory::get_all(dir_modules, '%^.*\._s$%');
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
      $c_parsed = static::settings_to_code($c_file->load(), $c_file->get_path_relative());
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

  ###############################
  ### operations with changes ###
  ###############################

  function changes_register_action($module_id, $action, $npath, $value = null, $rebuild = true) {
  # add new action
    $changes_d = dynamic::get('changes') ?: [];
    $changes_d[$module_id]->{$action}[$npath] = $value;
    dynamic::set('changes', $changes_d, ['build' => factory::datetime_get()]);
  # prevent opcache work
    static::$changes_dynamic['changes'] = $changes_d;
    if ($rebuild) {
      static::settings_cache_rebuild();
    }
  }

  function changes_unregister_action($module_id, $action, $npath) {
  }

  static function changes_apply_to_settings($changes, &$data) {
    foreach ($changes as $module_id => $c_module_changes) {
      foreach ($c_module_changes as $c_action_id => $c_changes) {
        foreach ($c_changes as $c_npath => $c_value) {
          $path_parts = explode('/', $c_npath);
          $child_name = array_pop($path_parts);
          $parent_obj = &factory::npath_get_pointer(implode('/', $path_parts), $data);
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

  ###############
  ### parsing ###
  ###############

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

  static function settings_to_code($data, $file_name = '') {
    $return = new \stdClass();
    $p = [-1 => &$return];
    $pc_objects = [];
    $pi_objects = [];
    $line_num = 0;
    foreach (explode(nl, $data) as $c_line) {
      $line_num++;
    # skip comments
      if (substr(ltrim($c_line, ' '), 0, 1) === '#') continue;
    # ─────────────────────────────────────────────────────────────────────
    # valid strings        | description
    # ─────────────────────────────────────────────────────────────────────
    # root                 | root element
    # - name: value        | root item     as null|string|float|integer|boolean
    #   name: value        | root property as null|string|float|integer|boolean
    # - name               | root item     as array|object[stdClass]
    #   name               | root property as array|object[stdClass]
    # - name|classname     | root item     as object[classname]
    #   name|classname     | root property as object[classname]
    # - name|_empty_array  | root item     as empty array
    #   name|_empty_array  | root property as empty array
    # ─────────────────────────────────────────────────────────────────────
      $matches = [];
      preg_match('%^(?<indent>[ ]*)'.
                   '(?<prefix>- |)'.
                   '(?<name>.+?)'.
                   '(?<delimiter>(?<!\\\\): |(?<!\\\\)\\||$)'.
                   '(?<value>.*)$%S', $c_line, $matches);
      if ($matches['name']) {
        $c_depth = intval(strlen($matches['indent'].$matches['prefix']) / 2);
        $matches['name'] = str_replace(['\\:', '\\|'], [':', '|'], $matches['name']);
      # define current value
        if ($matches['delimiter'] == ': ') {
          $c_value = $matches['value'];
          if (is_numeric($c_value)) $c_value += 0;
          if ($c_value === 'true')  $c_value = true;
          if ($c_value === 'false') $c_value = false;
          if ($c_value === 'null')  $c_value = null;
        } else {
          if ($matches['value'] == '_empty_array') {
            $c_value = [];
          } else {
            $c_class_name = $matches['value'] ? '\\effectivecore\\'.$matches['value'] : 'stdClass';
            $c_reflection = new \ReflectionClass($c_class_name);
            $c_is_pc = $c_reflection->implementsInterface('\\effectivecore\\post_constructor');
            $c_is_pi = $c_reflection->implementsInterface('\\effectivecore\\post_init');
            if ($c_is_pc) $c_value = factory::class_get_new_instance($c_class_name);
            else          $c_value = factory::class_get_new_instance($c_class_name, [], true);
            if ($c_is_pc) $pc_objects[] = $c_value;
            if ($c_is_pi) $pi_objects[] = $c_value;
          }
        }
      # add new item to tree
        if (is_array($p[$c_depth-1])) {
          $p[$c_depth-1][$matches['name']] = $c_value;
          $p[$c_depth] = &$p[$c_depth-1][$matches['name']];
        } else {
          $p[$c_depth-1]->{$matches['name']} = $c_value;
          $p[$c_depth] = &$p[$c_depth-1]->{$matches['name']};
        }
      # convert parent item to array
        if ($matches['prefix'] == '- ' && !is_array($p[$c_depth-1])) {
          $p[$c_depth-1] = (array)$p[$c_depth-1];
        }
      } else {
        $messages = ['Function: settings_to_code', 'Wrong syntax in settings data at line: '.$line_num];
        if ($file_name) $messages[] = 'File name: '.$file_name;
        message::add_new(implode(br, $messages), 'error');
      }
    }
  # call required functions
    foreach ($pc_objects as $c_object) $c_object->__construct();
    foreach ($pi_objects as $c_object) $c_object->init();
    return $return;
  }

}}