<?php

namespace effectivecore {

  const format_date     = 'Y-m-d';
  const format_time     = 'H:i:s';
  const format_datetime = 'Y-m-d H:i:s';
  const dir_root        = __DIR__;
  const dir_cache       = __DIR__.'/cache';
  const dir_modules     = __DIR__.'/modules';
  const dir_settings    = __DIR__.'/settings';
  const nl              = "\n";
  const br              = "<br/>";

  require_once('modules/core/classes/abstract--core.php');
  core::init();

}