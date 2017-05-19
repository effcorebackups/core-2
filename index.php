<?php

namespace effectivecore {

  const format_date     = 'Y-m-d';
  const format_time     = 'H:i:s';
  const format_datetime = 'Y-m-d H:i:s';
  const dir_root        = __DIR__.'/';
  const dir_cache       = __DIR__.'/cache/';
  const dir_modules     = __DIR__.'/modules/';
  const dir_settings    = __DIR__.'/settings/';
  const nl              = "\n";
  const br              = "<br/>";

  require_once('modules/core/classes/gl--file.php');
  require_once('modules/core/classes/gl--factory.php');
  require_once('modules/core/classes/gl--factory--timer.php');
  require_once('modules/core/classes/gl--factory--cache.php');
  require_once('modules/core/classes/gl--factory--files.php');
  spl_autoload_register('\effectivecore\factory::autoload');
  use \effectivecore\urls_factory as urls;
  use \effectivecore\token_factory as token;
  use \effectivecore\settings_factory as settings;
  use \effectivecore\timer_factory as timer;
  use \effectivecore\console_factory as console;
  use \effectivecore\events_factory as events;

  # redirect from '/any_path/' to '/any_path'
  if (urls::get_current()->path != '/' && substr(urls::get_current()->path, -1) == '/') {
    urls::go(
      rtrim(urls::get_current()->path, '/')
    );
  }

  # single entry point
  $file_types = [];
  foreach (settings::get('file_types') as $c_types) {
    foreach ($c_types as $c_name => $c_info) {
      $file_types[$c_name] = $c_info;
    }
  }
  $extension = urls::get_current()->get_extension();
  if ($extension) {
    # case for protected files
    if (!empty($file_types[$extension]->protected)) {
      factory::send_header_and_exit('access_denided',
        'Any file with this extension is protected by settings in file_types!'
      );
    }
    # case for media files
    $path = dir_root.ltrim(urls::get_current()->path, '/');
    if (is_file($path) && is_readable($path)) {
      $data = (new file($path))->load();
      if (isset($file_types[$extension]->mime)) header('Content-type: '.$file_types[$extension]->mime, true);
      if (isset($file_types[$extension]->use_tokens)) $data = token::replace($data);
      print $data;
      exit();
    }
  }

  # case for page (non file)
  timer::tap('total');
  foreach (events::get()->on_init as $c_info) {
    $c_handler = $c_info->handler;
    timer::tap($c_handler);
    call_user_func($c_handler);
    timer::tap($c_handler);
    console::add_log(
      'Call', $c_handler, '-', timer::get_period($c_handler, 0, 1)
    );
  }

}