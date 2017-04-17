<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\settings;
          use \effectivecore\messages;
          use \effectivecore\modules\data\db;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    session::init();
  }

  static function on_install() {
    db::transaction_begin(); # @todo: test transactions
    try {
      foreach (settings::$data['entities']['user']  as $c_entity)   $c_entity->install();
      foreach (settings::$data['instances']['user'] as $c_instance) $c_instance->insert();
      db::transaction_commit();
      messages::add_new('Database for module "user" was installed');
    } catch (\Exception $e) {
      db::transaction_roll_back();
    }
  }

}}