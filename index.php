<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {

  const format_date     = 'Y-m-d';
  const format_time     = 'H:i:s';
  const format_datetime = 'Y-m-d H:i:s';
  const dir_root        = __DIR__.'/';
  const dir_dynamic     = __DIR__.'/dynamic/';
  const dir_modules     = __DIR__.'/modules/';
  const nl              = "\n";
  const br              = "<br/>";

  require_once('modules/core/backend/class--gl--file.php');
  require_once('modules/core/backend/factory--gl--timer.php');
  require_once('modules/core/backend/factory--gl.php');
  require_once('modules/core/backend/factory--gl--cache.php');
  require_once('modules/core/backend/factory--gl--file.php');
  require_once('modules/core/backend/factory--gl--console.php');
  require_once('modules/core/backend/factory--gl--message.php');
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
      $file = new file($path);
      $data = $file->load();
    # apply tokens
      if (isset($file_types[$extension]->use_tokens)) {
        $data = tokens::replace($data);
      }
    # if get header HTTP_IF_NONE_MATCH
      $etag = base64_encode(md5($data, true));
      if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
                $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
        header('HTTP/1.1 304 Not Modified');
        exit();
      }
    # send headers
      header('Cache-Control: must-revalidate, private', true);
      header('Etag: '.$etag, true);
      if (is_array($file_types[$extension]->headers)) {
        foreach ($file_types[$extension]->headers as $c_key => $c_value) {
          header($c_key.': '.$c_value, true);
        }
      }
      print $data;
      exit();
    }
  }

  # case for page (non file)
  ob_start();
  foreach (events::get()->on_module_start as $c_info) {
    $c_handler = $c_info->handler;
    timers::tap($c_handler);
    $c_result = call_user_func($c_handler);
    if ($c_result) {
      print str_replace("\n\n", '', $c_result);
    }
    timers::tap($c_handler);
    console::add_log(
      'Call', $c_handler, '-', timers::get_period($c_handler, 0, 1)
    );
  }

}