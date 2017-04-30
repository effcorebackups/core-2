<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\settings_factory as settings;
          use \effectivecore\messages_factory as messages;
          use effectivecore\modules\user\session_factory as session;
          use \effectivecore\modules\storage\db;
          abstract class events_module_factory extends \effectivecore\events_module_factory {

  static function on_init() {
    session::init();
  }

  static function on_install() {
    foreach (settings::$data['entities']['user'] as $c_entity) $c_entity->install();
    foreach (settings::$data['entities_instances']['user'] as $c_instance) $c_instance->insert();
    messages::add_new('Database for module "user" was installed');
  }

}}