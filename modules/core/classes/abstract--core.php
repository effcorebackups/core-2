<?php

namespace effectivecore {
          abstract class core {

  static function init() {
    require_once('abstract--cache.php');
    require_once('abstract--factory.php');
    require_once('file.php');
    spl_autoload_register('\effectivecore\factory::autoload');
  # classes initialization
    settings::init();
    token::init();
    urls::init();
  # redirect from '/any_path/' to '/any_path'
    if (urls::$current->path != '/' && substr(urls::$current->path, -1) == '/') {
      $right_url = clone urls::$current;
      $right_url->path = rtrim($right_url->path, '/');
      urls::go($right_url->full());
    }
  # single entry point
    $file_types = [];
    foreach (settings::$data['file_types'] as $c_types) {
      foreach ($c_types as $c_type_name => $c_type_info) {
        $file_types[$c_type_name] = $c_type_info;
      }
    }
    $ext = urls::$current->extension();
    if ($ext) {
      if (!empty($file_types[$ext]->protected)) {
      # file existence is not checking - show access denied messge if url has any protected extension
        factory::send_header_and_exit('access_denided',
          'Any file with this extension is protected by settings in file_types!'
        );
      }
      $path = dir_root.urls::$current->path;
      if (is_file($path) && is_readable($path)) {
        $data = (new file($path))->load();
        if (isset($file_types[$ext]->mime)) header('Content-type: '.$file_types[$ext]->mime, true);
        if (isset($file_types[$ext]->use_tokens)) $data = token::replace($data);
        print $data;
        exit();
      }
    }
  # init modules
    ob_start();
    $call_stack = factory::collect_by_property(settings::$data['module'], 'on_init', 'id');
    foreach (factory::array_sort_by_weight($call_stack) as $module_id => $c_event) {
      console::set_log('Module ID = '.$module_id, $c_event->handler, 'Init calls');
      call_user_func($c_event->handler);
    }
  }

}}