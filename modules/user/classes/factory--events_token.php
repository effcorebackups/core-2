<?php

namespace effectivecore\modules\user {
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\translate_factory as translate;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_token_factory extends \effectivecore\events_token_factory {

  static function on_replace($match, $arg_1_num = null) {
    if (!empty(user::$current->id)) {
      switch ($match) {
        case '%%_user_id'   : return user::$current->id;
        case '%%_user_email': return user::$current->email;
        case '%%_context_user_mail':
          $arg_1_value = urls::$current->get_args($arg_1_num);
          if (user::$current->id == $arg_1_value) {
            return translate::t('my account');
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