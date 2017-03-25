<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class events {

  static function on_show_modules() {
    $head = ['Title', 'Id', 'Path', 'Description', 'Version', 'Is embed', 'Is always on'];
    $data = [];
    foreach (settings::$data['module'] as $c_module) {
      $data[] = [
        $c_module->title,
        $c_module->id,
        $c_module->path,
        $c_module->description,
        $c_module->version,
        $c_module->is_embed ? 'Yes' : 'No',
        $c_module->is_always_on ? 'Yes' : 'No'
      ];
    }
    $data_markup = new html_table([], $data, $head);
    page::add_element($data_markup);
  }

}}