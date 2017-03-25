<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\message;
          use \effectivecore\modules\data\db;
          abstract class events_token extends \effectivecore\events_token {

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