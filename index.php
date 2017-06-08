<?php

namespace effectivecore {

  # minimal PHP: 5.6.0
  # tested on PHP: 5.6.0
  # tested on PHP: 7.1.0

  const format_date     = 'Y-m-d';
  const format_time     = 'H:i:s';
  const format_datetime = 'Y-m-d H:i:s';
  const dir_root        = __DIR__.'/';
  const dir_dynamic     = __DIR__.'/dynamic/';
  const dir_modules     = __DIR__.'/modules/';
  const nl              = "\n";
  const br              = "<br/>";

  require_once('modules/core/classes/gl--factory--timer.php');
  require_once('modules/core/classes/gl--file.php');
  require_once('modules/core/classes/gl--factory.php');
  require_once('modules/core/classes/gl--factory--cache.php');
  require_once('modules/core/classes/gl--factory--file.php');
  require_once('modules/core/classes/gl--factory--console.php');
  require_once('modules/core/classes/gl--factory--message.php');
  spl_autoload_register('\effectivecore\factory::autoload');
  use \effectivecore\url_factory as urls;
  use \effectivecore\token_factory as tokens;
  use \effectivecore\timer_factory as timers;
  use \effectivecore\console_factory as console;
  use \effectivecore\modules\storage\storage_factory as storages;
  timers::tap('total');

  # redirect from '/any_path/' to '/any_path'
  if (urls::get_current()->path != '/' && substr(urls::get_current()->path, -1) == '/') {
    urls::go(
      rtrim(urls::get_current()->path, '/')
    );
  }

  ##########################
  ### single entry point ###
  ##########################

  $file_types = [];
  foreach (storages::get('settings')->select('file_types') as $c_types) {
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
      if (isset($file_types[$extension]->use_tokens)) $data = tokens::replace($data);
      print $data;
      exit();
    }
  }

  # case for page (non file)
  ob_start();
  foreach (events::get()->on_start as $c_info) {
    $c_handler = $c_info->handler;
    timers::tap($c_handler);
    $c_result = call_user_func($c_handler);
    if ($c_result) {
      print $c_result;
    }
    timers::tap($c_handler);
    console::add_log(
      'Call', $c_handler, '-', timers::get_period($c_handler, 0, 1)
    );
  }

}