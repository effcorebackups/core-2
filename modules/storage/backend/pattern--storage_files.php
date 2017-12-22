<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          class storage_files {

  static public $data_orig;
  static public $data = [];
  static public $changes_dynamic;

  static function init($group) {
    console::add_log('storage', 'init.', 'storage %%_id will be initialized', 'ok', 0, ['id' => $group.' | storage_files']);
    $cache = cache::select('data--'.$group);
    if ($cache) static::$data[$group] = $cache;
    else        static::data_cache_rebuild();
  }

  ########################
  ### shared functions ###
  ########################

  function select_group($group) {
    if   (!isset(static::$data[$group])) static::init($group);
    return isset(static::$data[$group]) ?
                 static::$data[$group] : null;
  }

  function select_by_datapath($datapath) {
    $datapath_parts = explode('/', $datapath);
    $group_name = array_shift($datapath_parts);
    $group_data = $this->select_group($group_name);
    return factory::datapath_get_object(implode('/', $datapath_parts), $group_data);
  }

  ######################
  ### data functions ###
  ######################

  static function data_cache_rebuild() {
  # init original data
    $data_orig = cache::select('data_original');
    if (!$data_orig) {
      static::$data_orig = $data_orig = static::data_find_static();
      cache::update('data_original', $data_orig, ['build' => factory::datetime_get()]);
    }
  # init dynamic and static changes
    $changes_d = dynamic::select('changes') ?: [];
    $changes_s = isset($data_orig['changes']) ? $data_orig['changes'] : [];
  # apply all changes to original data and get final data
    $data = factory::array_deep_clone($data_orig);
    static::changes_apply_to_data($changes_d, $data);
    static::changes_apply_to_data($changes_s, $data);
    unset($data['changes']);
  # save cache
    foreach ($data as $group => $slice) {
      cache::update('data--'.$group, $slice);
      static::$data[$group] = $slice;
    }
  }

  static function data_find_static() {
    $return = [];
    $files = file::select_all(dir_modules, '%^.*\._d$%');
    $modules_path = [];
    foreach ($files as $c_file) {
      if ($c_file->get_file() == 'module._d') {
        $modules_path[$c_file->get_name_parent()] = $c_file->get_dirs_relative();
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
      $c_parsed = static::data_to_code(
        $c_file->load(),
        $c_file->get_path_relative());
      foreach ($c_parsed as $c_type => $c_data) {
        if (is_object($c_data)) {
          if ($c_type == 'module') $c_data->path = $modules_path[$c_scope];
          $return[$c_type][$c_scope] = $c_data;
        }
        if (is_array($c_data)) {
          if (!isset($return[$c_type][$c_scope]))
                     $return[$c_type][$c_scope] = [];
          $return[$c_type][$c_scope] += $c_data;
        }
      }
    }
    return $return;
  }

  #########################
  ### changes functions ###
  #########################

  function changes_register_action($module_id, $action, $datapath, $value = null, $rebuild = true) {
  # add new action
    $changes_d = dynamic::select('changes') ?: [];
    $changes_d[$module_id]->{$action}[$datapath] = $value;
    dynamic::update('changes', $changes_d, ['build' => factory::datetime_get()]);
  # prevent opcache work
    static::$changes_dynamic['changes'] = $changes_d;
    if ($rebuild) {
      static::data_cache_rebuild();
    }
  }

  function changes_unregister_action($module_id, $action, $datapath) {
  }

  static function changes_apply_to_data($changes, &$data) {
    foreach ($changes as $module_id => $c_module_changes) {
      foreach ($c_module_changes as $c_action_id => $c_changes) {
        foreach ($c_changes as $c_datapath => $c_value) {
          $c_datapath_parts = explode('/', $c_datapath);
          $c_child_name = array_pop($c_datapath_parts);
          $c_parent_obj = &factory::datapath_get_pointer(implode('/', $c_datapath_parts), $data);
          switch ($c_action_id) {
            case 'insert': # only structured types support (array|object)
              switch (gettype($c_parent_obj)) {
                case 'array' : $destination_obj = &$c_parent_obj[$c_child_name];   break;
                case 'object': $destination_obj = &$c_parent_obj->{$c_child_name}; break;
              }
              switch (gettype($destination_obj)) {
                case 'array' : foreach ($c_value as $key => $value) $destination_obj[$key]   = $value; break;
                case 'object': foreach ($c_value as $key => $value) $destination_obj->{$key} = $value; break;
              }
              break;
            case 'update': # only scalar types support (string|numeric) @todo: test bool|null
              switch (gettype($c_parent_obj)) {
                case 'array' : $c_parent_obj[$c_child_name]   = $c_value; break;
                case 'object': $c_parent_obj->{$c_child_name} = $c_value; break;
              }
              break;
            case 'delete':
              switch (gettype($c_parent_obj)) {
                case 'array' : unset($c_parent_obj[$c_child_name]);   break;
                case 'object': unset($c_parent_obj->{$c_child_name}); break;
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

  static function code_to_data($code, $entity_name = '', $entity_prefix = '  ', $depth = 0) {
    $return = [];
    if ($entity_name) {
      $return[] = str_repeat('  ', $depth-1).($depth ? $entity_prefix : '').$entity_name;
    }
    foreach ($code as $key => $value) {
      if (is_array($value)  && !count($value))           continue;
      if (is_object($value) && !get_object_vars($value)) continue;
      if (is_array($value))       $return[] = static::code_to_data($value, $key, is_array($code) ? '- ' : '  ', $depth + 1);
      else if (is_object($value)) $return[] = static::code_to_data($value, $key, is_array($code) ? '- ' : '  ', $depth + 1);
      else if ($value === null)   $return[] = str_repeat('  ', $depth).(is_array($code) ? '- ' : '  ').$key.': null';
      else if ($value === false)  $return[] = str_repeat('  ', $depth).(is_array($code) ? '- ' : '  ').$key.': false';
      else if ($value === true)   $return[] = str_repeat('  ', $depth).(is_array($code) ? '- ' : '  ').$key.': true';
      else                        $return[] = str_repeat('  ', $depth).(is_array($code) ? '- ' : '  ').$key.': '.$value;
    }
    return implode(nl, $return);
  }

  static function data_to_code($data, $file_name = '') {
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
      if (strlen($matches['name'])) {
        $c_depth = intval(strlen($matches['indent'].$matches['prefix']) / 2);
        $matches['name'] = str_replace(['\\:', '\\|'], [':', '|'], $matches['name']);
      # define each value
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
        $messages = ['Function: data_to_code', 'Wrong syntax in data at line: '.$line_num];
        if ($file_name) $messages[] = 'File name: '.$file_name;
        message::insert(implode(br, $messages), 'error');
      }
    }
  # call required functions
    foreach ($pc_objects as $c_object) $c_object->__construct();
    foreach ($pi_objects as $c_object) $c_object->init();
    return $return;
  }

}}