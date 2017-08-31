<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_page extends events {

  static function on_show_admin_modules() {
    $head = [[
      'Title',
      'ID',
      'Path',
      'Description',
      'Version',
      'State',
    ]];
    $body = [];
    foreach (storages::get('settings')->select('module') as $c_module) {
      $body[] = [
        $c_module->title,
        $c_module->id,
        $c_module->path,
        $c_module->description,
        $c_module->version,
        $c_module->state,
      ];
    }
    return new table([], $body, $head);
  }

}}