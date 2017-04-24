<?php

namespace effectivecore\modules\user {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_token extends \effectivecore\events_token {

  static function on_replace($match) {
    if (!empty(user::$current->id)) {
      switch ($match) {
        case '%%_user_id'   : return user::$current->id;
        case '%%_user_email': return user::$current->email;
        case '%%_profile_title':
          if (user::$current->id == urls::$current->get_args(2)) {
            return 'My profile';
          } else {
            $db_user = table_user::select_one(['email'], ['id' => urls::$current->get_args(2)]);
            return 'Profile of '.$db_user['email'];
          }
        case '%%_profile_edit_title':
          if (user::$current->id == urls::$current->get_args(2)) {
            return 'Edit my profile';
          } else {
            $db_user = table_user::select_one(['email'], ['id' => urls::$current->get_args(2)]);
            return 'Edit profile of '.$db_user['email'];
          }
      }
    }
  }

}}