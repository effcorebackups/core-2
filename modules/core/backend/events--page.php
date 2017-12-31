<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\core {
          use const \effectivecore\br;
          use \effectivecore\table as table;
          use \effectivecore\markup as markup;
          use \effectivecore\module as module;
          use \effectivecore\locale as locale;
          use \effectivecore\translation as translation;
          use \effectivecore\control_switcher as switcher;
          use \effectivecore\table_body_row_cell as table_body_row_cell;
          use \effectivecore\control_actions_list as control_actions_list;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_modules($page) {
    $thead = [['Module information', '']];
    $tbody = [];
    foreach (module::get_all() as $c_module) {
      $c_action_list = new control_actions_list([], [], null);
      $c_action_list->action_add('/admin/modules/'.$c_module->id.'/enable', 'enable',       $c_module->state != 'always_on');
      $c_action_list->action_add('/admin/modules/'.$c_module->id.'/disable', 'disable',     $c_module->state != 'always_on');
      $c_action_list->action_add('/admin/modules/'.$c_module->id.'/install', 'install',     $c_module->state != 'always_on');
      $c_action_list->action_add('/admin/modules/'.$c_module->id.'/uninstall', 'uninstall', $c_module->state != 'always_on');
      $tbody[] = [
        new table_body_row_cell(['class' => ['info' => 'info']],
          translation::get('ID')         .': '.$c_module->id.br.
          translation::get('Version')    .': '.locale::format_version($c_module->version).br.
          translation::get('Title')      .': '.translation::get($c_module->title).br.
          translation::get('Description').': '.translation::get($c_module->description).br.
          translation::get('Path')       .': '.$c_module->path),
        new table_body_row_cell(['class' => ['actions' => 'actions']], $c_action_list)
      ];
    }
    return new markup('x-block', ['id' => 'modules_admin'], [
      new table([], $tbody, $thead)
    ]);
  }

}}