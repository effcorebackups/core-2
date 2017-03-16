<?php

namespace effectivecore {
          abstract class settings {

  static $data;

  static function init() {
    $file = new file(dir_cache.'/settings.php');
    if ($file->is_exist()) $file->insert();
    else static::_update();
  }

  static protected function _update() {
    $parse = [];
    $files = file::get_all(dir_modules, '%^.*\._s$%') +
             file::get_all(dir_settings, '%^.*\._s$%');
    $modules = [];
    foreach ($files as $c_file) {
      if ($c_file->name_full == 'module._s') {
        $modules[$c_file->path] = $c_file->parent_dir;
      }
    }
    foreach ($files as $c_file_path => $c_file) {
      $c_scope = 'global';
      foreach ($modules as $c_module_path => $c_module_id) {
        if (strpos($c_file_path, $c_module_path) === 0) {
          $c_scope = $c_module_id;
          break;
        }
      }
      foreach (parser::parse_settings($c_file->load()) as $c_type => $c_data) {
        if (is_object($c_data)) {
          $parse[$c_type][$c_scope] = $c_data;
        }
        if (is_array($c_data)) {
          if (!isset($parse[$c_type][$c_scope])) $parse[$c_type][$c_scope] = [];
          $parse[$c_type][$c_scope] += $c_data;
        }
      }
    }
    $file = new file(dir_cache.'/settings.php');
    $file->content = "<?php \n\nnamespace effectivecore { # settings::\$data[entity_type][scope]...\n\n".
                       factory::data_export($parse, '  settings::$data').
                     "\n}";
    $file->save();
    factory::send_header_and_exit('page_refresh',
      'Make cache directory writable if you see this message!', 0
    );
  }

}}