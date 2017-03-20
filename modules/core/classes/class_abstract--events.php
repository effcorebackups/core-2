<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class events {

  static function on_page_install() {
  # if (version_compare(phpversion(), '5.6.0') < 0) print "PHP is too old!\nCurrent version: ".phpversion()."\nRequired version: 5.6.0+";
    $call_stack = factory::collect_by_property(settings::$data['module'], 'on_install');
    foreach (factory::array_sort_by_weight($call_stack) as $c_event) {
      call_user_func($c_event->handler);
    }
  }

  static function on_page_modules() {
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
    $data_markup = new html_table([], $data, ['Title', 'Id', 'Path', 'Description', 'Version', 'Is embed', 'Is always on']);
    page::add_element($data_markup);
  }

}}