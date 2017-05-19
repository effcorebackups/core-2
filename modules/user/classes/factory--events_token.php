<?php

namespace effectivecore\modules\user {
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\translate_factory as translate;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_token_factory extends \effectivecore\events_token_factory {

  static function on_replace($match, $arg_1_num = null) {
    if (!empty(user::get_current()->id)) {
      switch ($match) {
        case '%%_user_id'   : return user::get_current()->id;
        case '%%_user_email': return user::get_current()->email;
        case '%%_context_user_mail':
          $arg_1_value = urls::get_current()->get_args($arg_1_num);
          if (user::get_current()->id == $arg_1_value) {
            return translate::get('my account');
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