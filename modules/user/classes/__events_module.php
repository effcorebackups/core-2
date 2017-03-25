<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\message;
          use \effectivecore\modules\data\db;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    session::init();
  }

  static function on_install() {
    db::transaction_begin(); # @todo: test transactions
    try { 
      table_session::install();
      table_user::install();
      table_role::install();
      table_permission::install();
      table_role_by_user::install();
      table_role_by_permission::install();
      db::transaction_commit();
      message::set('Database for module "user" was installed');
    } catch (\Exception $e) {
      db::transaction_roll_back();
    }
  }

}}