<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\user\session_factory as session;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    session::init();
  }

  static function on_install() {
  # install entities
    $entities = storages::get('settings')->select_group('entities')['user'];
    foreach ($entities as $c_entity) {
      if ($c_entity->install()) messages::add_new(translations::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      messages::add_new(translations::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  # insert instances
    $instances = storages::get('settings')->select_group('instances')['user'];
    foreach ($instances as $c_instance) {
      if ($c_instance->insert()) messages::add_new(translations::get('Instances of entity %%_name was added.',     ['name' => $c_entity->get_name()]));
      else                       messages::add_new(translations::get('Instances of entity %%_name was not added!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}