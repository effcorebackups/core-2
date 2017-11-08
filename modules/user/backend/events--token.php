<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\instance as instance;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\user\users_factory as users;
          abstract class events_token extends \effectivecore\events_token {

  static function on_replace($match, $args = []) {
    if (!empty(users::get_current()->id)) {
      switch ($match) {
        case '%%_id_user': return users::get_current()->id;
        case '%%_email'  : return users::get_current()->email;
        case '%%_nick'   : return users::get_current()->nick;
        case '%%_id_user_context':
        case '%%_email_context':
        case '%%_nick_context':
          $arg_num = $args[0];
          $id_user = urls::get_current()->get_args($arg_num);
          $user = (new instance('user', ['id' => $id_user]))->select();
          if ($user && $match == '%%_id_user_context') return $user->id;
          if ($user && $match == '%%_email_context')   return $user->email;
          if ($user && $match == '%%_nick_context')    return $user->nick;
          return '[unknown uid]';
      }
    }
  }

}}