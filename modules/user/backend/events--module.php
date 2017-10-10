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
    foreach (storages::get('settings')->select_group('entities')['user'] as $c_entity) $c_entity->install();
    foreach (storages::get('settings')->select_group('entities_instances')['user'] as $c_instance) $c_instance->insert();
    messages::add_new(
      translations::get('Tables for module %%_name was installed.', ['name' => 'user'])
    );
  }

}}