<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\factory as factory;
          use \effectivecore\instance as instance;
          use \effectivecore\message_factory as message;
          use \effectivecore\modules\user\user_factory as user;
          abstract class session_factory {

  static function init($id_user = null) {
  # create session after login or register new user
    if ($id_user != null) {
      session_start();
      (new instance('session', [
        'id'      => session_id(),
        'id_user' => $id_user,
        'created' => factory::datetime_get()
      ]))->insert();
    }
  # restore session for authenticated user
    if ($id_user == null && isset($_COOKIE[session_name()])) {
      $session = (new instance('session', [
        'id' => $_COOKIE[session_name()]
      ]))->select();
      if ($session &&
          $session->id_user) {
        session_start();
        $id_user = $session->id_user;
      } else {
      # remove lost or fake sid in browser
        setcookie(session_name(), '', 0, '/');
        message::add_new('invalid session was deleted!', 'warning');
      }
    }
  # init user
    user::init($id_user);
  }

  static function destroy($id_user) {
    (new instance('session', [
      'id'      => session_id(),
      'id_user' => $id_user
    ]))->delete();
    setcookie(session_name(), '', 0, '/');
    session_destroy();
  }

}}