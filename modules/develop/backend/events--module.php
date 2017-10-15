<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
  }

  static function on_install() {
    $entities = storages::get('settings')->select_group('entities')['develop'];
    foreach ($entities as $c_entity) {
      if ($c_entity->install()) messages::add_new(translations::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      messages::add_new(translations::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}