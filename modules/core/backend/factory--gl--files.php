<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          abstract class files_factory {

  # path parts:
  # ─────────────────────────────────────────────────────────────────────
  # /var/www/modules/core/          - dirs->full
  #          modules/core/          - dirs->relative
  # /var/www/modules/core/file.type - dirs->full     + file->full
  #          modules/core/file.type - dirs->relative + file->full
  #                      /file.type - dirs->full     + file->full
  #                       file.type - file->full
  #                       file      - file->name
  #                            type - file->type
  # ─────────────────────────────────────────────────────────────────────

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. if the first letter in the path is '/' - it's a full path, оtherwise - relative path
  # 2. if the last letter in the path is '/' - it's a directory, оtherwise - file
  # 3. path components like '../' is ignored!
  # 4. path components like './' is ignored!
  # 5. windows files naming rules is ignored!
  # ─────────────────────────────────────────────────────────────────────

  static function parse_path($path, &$obj = null) {
    if (!$obj) $obj = new \stdClass();
    $obj->original = $path;
    $obj->file = new \stdClass;
    $obj->dirs = new \stdClass;
    $obj->file->full = '';
    $obj->file->name = '';
    $obj->file->type = '';
    $obj->dirs->full = '';
    $obj->dirs->relative = '';
    $char_0 = $path[0];
    $char_N = $path[strlen($path)-1];
  # parse
    if ($char_N == '/') {
      $obj->dirs->full = rtrim($path, '/');
    } else {
      $obj->file->full = ltrim(strrchr($path, '/') ? : $path, '/');
      $obj->file->type = ltrim(strrchr($obj->file->full, '.') ? : '', '.');
      $obj->file->name = rtrim(substr($obj->file->full, 0, -strlen($obj->file->type)), '.');
      $obj->dirs->full = rtrim(substr($path, 0, -strlen($obj->file->full)), '/');
    }
  # define relative path
    if ($char_0 != '/') {
      $obj->dirs->relative = $obj->dirs->full;
    } elseif (dir_root === substr($obj->dirs->full, 0, strlen(dir_root))) {
      $obj->dirs->relative = substr($obj->dirs->full, strlen(dir_root));
    }
    return $obj;
  }

  static function get_all($parent_dir, $filter = '') {
    $files = [];
    foreach (scandir($parent_dir) as $c_name) {
      if ($c_name != '.' &&
          $c_name != '..') {
        if (is_file($parent_dir.$c_name)) {
          if (!$filter || ($filter && preg_match($filter, $parent_dir.$c_name))) {
            $files[$parent_dir.$c_name] = new file($parent_dir.$c_name);
          }
        } elseif (is_dir($parent_dir.$c_name)) {
          $files += static::get_all($parent_dir.$c_name.'/', $filter);
        }
      }
    }
    return $files;
  }

}}