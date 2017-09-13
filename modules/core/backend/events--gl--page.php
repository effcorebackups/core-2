<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\locales_factory as locales;
          use \effectivecore\control_switcher as switcher;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_page {

  static function on_show_admin_modules() {
    $thead = [['Title', 'ID', 'Path', 'Description', 'Ver.', '']];
    $tbody = [];
    foreach (storages::get('settings')->select('module') as $c_module) {
      $tbody[] = [
        $c_module->title,
        $c_module->id,
        $c_module->path,
        $c_module->description,
        locales::format_version($c_module->version),
        new switcher($c_module->state),
      ];
    }
    return new table([], $tbody, $thead);
  }

}}