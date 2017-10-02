<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          use \effectivecore\factory as factory;
          use \effectivecore\instance as instance;
          use \effectivecore\modules\user\users_factory as users;
          abstract class session_factory {

  static function init($user_id = 0) {
  # renew session for user with selected id
    if ($user_id != 0) {
      session_start();
      (new instance('session', [
        'id'      => session_id(),
        'user_id' => $user_id,
        'created' => factory::datetime_get_curent()
      ]))->insert();
    }
  # restore session for authenticated user
    if ($user_id == 0 && isset($_COOKIE[session_name()])) {
      $session = (new instance('session', [
        'id' => $_COOKIE[session_name()]
      ]))->select();
      if ($session) {
        $user_id = $session->user_id;
        session_start();
      } else {
      # remove lost or substituted sid in browser
        setcookie(session_name(), '', 0, '/');
      }
    }
  # init user
    users::init($user_id);
  }

  static function destroy($user_id) {
    (new instance('session', [
      'id'      => session_id(),
      'user_id' => $user_id
    ]))->delete();
    setcookie(session_name(), '', 0, '/');
    session_destroy();
  }

}}