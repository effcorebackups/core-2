<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\user\user_factory as users;
          abstract class events_token extends \effectivecore\events_token {

  static function on_replace($match, $args = []) {
    if (!empty(users::get_current()->id)) {
      switch ($match) {
        case '%%_user_id'   : return users::get_current()->id;
        case '%%_user_email': return users::get_current()->email;
        case '%%_user_email_context':
          $url_arg = urls::get_current()->get_args($args[0]);
          if (users::get_current()->id == $url_arg) {
            return translations::get('my account');
          } else {
            $user = (new entity_instance('entities/user/user', [
              'id' => $url_arg
            ]))->select();
            return $user ?
                   $user->email : '[unknown uid]';
          }
      }
    }
  }

}}