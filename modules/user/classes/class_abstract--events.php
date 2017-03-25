<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\message;
          use \effectivecore\modules\data\db;
          abstract class events extends \effectivecore\events {

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

  static function on_token_replace($match) {
    switch ($match) {
      case '%%_user_id': return user::$current->id;
      case '%%_user_email': return user::$current->email;
      case '%%_profile_title':
        if (user::$current->id == url::$current->args('2')) {
          return 'My profile';
        } else {
          $db_user = table_user::select_first(['email'], ['id' => url::$current->args('2')]);
          return 'Profile of '.$db_user['email'];
        }
      case '%%_profile_edit_title':
        if (user::$current->id == url::$current->args('2')) {
          return 'Edit my profile';
        } else {
          $db_user = table_user::select_first(['email'], ['id' => url::$current->args('2')]);
          return 'Edit profile of '.$db_user['email'];
        }
    }
  }

}}