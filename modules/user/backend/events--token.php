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
        case '%%_user_id'        : return users::get_current()->id;
        case '%%_user_email'     : return users::get_current()->email;
        case '%%_user_email_name': return strstr(users::get_current()->email, '@', true).'@...';
        case '%%_user_id_context':
        case '%%_user_email_context':
        case '%%_user_email_name_context':
          $arg_num = $args[0];
          $user_id = urls::get_current()->get_args($arg_num);
          $user = (new instance('user', ['id' => $user_id]))->select();
          if ($user && $match == '%%_user_id_context')         return $user->id;
          if ($user && $match == '%%_user_email_context')      return $user->email;
          if ($user && $match == '%%_user_email_name_context') return strstr($user->email, '@', true).'@...';
          return '[unknown uid]';
      }
    }
  }

}}