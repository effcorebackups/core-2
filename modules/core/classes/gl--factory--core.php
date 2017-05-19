<?php

namespace effectivecore {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\token_factory as token;
          use \effectivecore\settings_factory as settings;
          abstract class core_factory {

  static function init() {    
  # redirect from '/any_path/' to '/any_path'
    if (urls::get_current()->path != '/' && substr(urls::get_current()->path, -1) == '/') {
      $right_url = clone urls::get_current();
      $right_url->path = rtrim($right_url->path, '/');
      urls::go($right_url->get_full());
    }
  # single entry point
    $file_types = [];
    foreach (settings::get('file_types') as $c_types) {
      foreach ($c_types as $c_type_name => $c_type_info) {
        $file_types[$c_type_name] = $c_type_info;
      }
    }
    $ext = urls::get_current()->get_extension();
    if ($ext) {
      if (!empty($file_types[$ext]->protected)) {
      # file existence is not checking - show access denied messge if url has any protected extension
        factory::send_header_and_exit('access_denided',
          'Any file with this extension is protected by settings in file_types!'
        );
      }
      $path = dir_root.ltrim(urls::get_current()->path, '/');
      if (is_file($path) && is_readable($path)) {
        $data = (new file($path))->load();
        if (isset($file_types[$ext]->mime)) header('Content-type: '.$file_types[$ext]->mime, true);
        if (isset($file_types[$ext]->use_tokens)) $data = token::replace($data);
        print $data;
        exit();
      }
    }
  }

}}