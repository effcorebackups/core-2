<?php

namespace effectivecore\modules\user {
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_token_factory extends \effectivecore\events_token_factory {

  static function on_replace($match) {
    if (!empty(user::$current->id)) {
      switch ($match) {
        case '%%_user_id'   : return user::$current->id;
        case '%%_user_email': return user::$current->email;

        case '%%_profile_title':
          if (user::$current->id ==
              urls::$current->get_args(2)) return 'My profile';
          else {
            $user = (new entity_instance('entities/user/user', [
              'id' => urls::$current->get_args(2)
            ]))->select();
            return 'Profile of '.($user ? $user->get_value('email') : '[UNKNOWN]');
          }

        case '%%_profile_edit_title':
          if (user::$current->id ==
              urls::$current->get_args(2)) return 'Edit my profile';
          else {
            $user = (new entity_instance('entities/user/user', [
              'id' => urls::$current->get_args(2)
            ]))->select();
            return 'Edit profile of '.($user ? $user->get_value('email') : '[UNKNOWN]');
          }
      }
    }
  }

}}