<?php

namespace effectivecore {
          use \effectivecore\modules\page\page_factory;
          abstract class events_page extends events {

  static function on_show_install() {
    foreach (static::$data->on_install as $c_event) {
      call_user_func($c_event->handler);
    }
  }

  static function on_show_modules() {
    $head = [
      'Title',
      'ID',
      'Path',
      'Description',
      'Version',
      'Is embed',
      'Is always on'
    ];
    $data = [];
    foreach (settings_factory::$data['module'] as $c_module) {
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
    page_factory::add_element(
      new table([], $data, [$head])
    );
  }

}}