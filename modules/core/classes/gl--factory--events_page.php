<?php

namespace effectivecore {
          use \effectivecore\settings_factory as settings;
          use \effectivecore\modules\page\page_factory as page;
          abstract class events_page_factory extends events_factory {

  static function on_show_install() {
    foreach (static::get()->on_install as $c_event) {
      call_user_func($c_event->handler);
    }
  }

  static function on_show_modules() {
    $head = [[
      'Title',
      'ID',
      'Path',
      'Description',
      'Version',
      'State',
    ]];
    $body = [];
    foreach (settings::get('module') as $c_module) {
      $body[] = [
        $c_module->title,
        $c_module->id,
        $c_module->path,
        $c_module->description,
        $c_module->version,
        $c_module->state,
      ];
    }
    page::add_element(
      new table([], $body, $head)
    );
  }

}}