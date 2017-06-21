<?php

namespace effectivecore\modules\user {
          use \effectivecore\url_factory as urls;
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\user\user_factory as users;
          abstract class events_token extends \effectivecore\events_token {

  static function on_replace($match, $arg_1_num = null) {
    if (!empty(users::get_current()->id)) {
      switch ($match) {
        case '%%_user_id'   : return users::get_current()->id;
        case '%%_user_email': return users::get_current()->email;
        case '%%_user_email_context':
          $arg_1_value = urls::get_current()->get_args($arg_1_num);
          if (users::get_current()->id == $arg_1_value) {
            return translations::get('my account');
          } else {
            $user = (new entity_instance('entities/user/user', [
              'id' => $arg_1_value
            ]))->select();
            if ($user) {
              return $user->email;
            } else {
              return '[UNKNOWN UID]';
            }
          }
      }
    }
  }

}}