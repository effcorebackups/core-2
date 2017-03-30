<?php

namespace effectivecore {
          abstract class files {

  # path parts:
  # ─────────────────────────────────────────────────────────────────────
  # /var/www/modules/core           - dirs->full
  #          modules/core           - dirs->relative
  # /var/www/modules/core/file.type - dirs->full     + file->full
  #          modules/core/file.type - dirs->relative + file->full
  #                       file.type - file->full
  #                       file      - file->name
  #                            type - file->type
  # ─────────────────────────────────────────────────────────────────────

  # wrong paths:
  # ─────────────────────────────────────────────────────────────────────
  # 1. '...core/file.type/' - should be writen as '...core/file.type'
  # 2. '...core/directory/' - should be writen as '...core/directory'
  # ─────────────────────────────────────────────────────────────────────

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. if the first letter in the path is '/' - it's the full path,
  #    оtherwise it's the relative path
  # 2. in other system the last letter '/' mean that entity is an directory
  #    but in this system only '.' defined is this file or directory
  # p.s.1 any file without extensions is the bad practice
  # p.s.2 using letter '/' at last is bad practice because it's the
  #       reason of many errors in the code
  # ─────────────────────────────────────────────────────────────────────

  static function parse_path($path) {
    $info = new \StdClass;
    $info->file = new \StdClass;
    $info->file->full = '';
    $info->file->name = '';
    $info->file->type = '';
    $info->dirs = new \StdClass;
    $info->dirs->full = '';
    $info->dirs->relative = '';
  # set file and dirs info
    $last_part = ltrim(strrchr($path, '/'), '/');
    $type = ltrim(strrchr($last_part, '.'), '.');
    if ($type) {
      $info->file->type = $type;
      $info->file->full = $last_part;
      $info->file->name = substr($last_part, 0, -1 - strlen($type));
      $info->dirs->full = substr($path, 0, -1 - strlen($last_part));
    } else {
      $info->dirs->full = $path;
    }
  # set relative dirs info
    if (dir_root === substr($info->dirs->full, 0, strlen(dir_root))) {
      $info->dirs->relative = substr($info->dirs->full, strlen(dir_root) + 2);
    }
    return $info;
  }

}}