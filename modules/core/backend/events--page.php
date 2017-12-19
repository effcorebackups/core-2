<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\core {
          use \effectivecore\table as table;
          use \effectivecore\locale as locale;
          use \effectivecore\storage as storage;
          use \effectivecore\control_switcher as switcher;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_modules($page) {
    $thead = [['Title', 'ID', 'Path', 'Description', 'Ver.', 'State.']];
    $tbody = [];
    foreach (storage::get('files')->select_group('module') as $c_module) {
      $tbody[] = [
        $c_module->title,
        $c_module->id,
        $c_module->path,
        $c_module->description,
        locale::format_version($c_module->version),
        $c_module->state != 'always_on' ? new switcher($c_module->state) : '&nbsp;',
      ];
    }
    return new table([], $tbody, $thead);
  }

}}