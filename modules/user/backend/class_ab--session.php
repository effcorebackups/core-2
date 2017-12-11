<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\factory as factory;
          use \effectivecore\message as message;
          use \effectivecore\instance as instance;
          abstract class session {

  static function id_regenerate($sign, $expire = 60 * 60 * 24 * 30) {
    $session_id = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].rand(0, PHP_INT_MAX));
    $session_id[0] = $sign; # a - anonymous user | f - authenticated user
    setcookie('session_id', ($_COOKIE['session_id'] = $session_id), $expire ? time() + $expire : 0, '/');
    setcookie('cookies_is_on', 'true', $expire ? time() + $expire : 0, '/');
    return $session_id;
  }

  static function id_get() {
    $c_value = filter_var(isset($_COOKIE['session_id']) ?
                                $_COOKIE['session_id'] : '', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '%^[0-9a-f]{32}$%']]);
    if ($c_value) return $c_value;
    else          return static::id_regenerate('a');
  }

  static function select() {
    $session_id = static::id_get();
    if ($session_id[0] == 'f') {
      $session = (new instance('session', [
        'id' => $session_id
      ]))->select();
      if (!$session ||
          ($session && $session->ip_address !== $_SERVER['REMOTE_ADDR']) ||
          ($session && $session->user_agent_hash !== md5($_SERVER['HTTP_USER_AGENT']))) {
        static::id_regenerate('a');
        message::insert('invalid session was deleted!', 'warning');
        return null;
      } else {
        return $session;
      }
    }
  }

  static function insert($id_user, $is_remember) {
    if ($is_remember) static::id_regenerate('f');
    else              static::id_regenerate('f', 0);
    (new instance('session', [
      'id'              => static::id_get(),
      'id_user'         => $id_user,
      'created'         => factory::datetime_get(),
      'ip_address'      => $_SERVER['REMOTE_ADDR'],
      'user_agent_hash' => md5($_SERVER['HTTP_USER_AGENT'])
    ]))->insert();
  }

  static function delete($id_user) {
    (new instance('session', [
      'id'      => static::id_get(),
      'id_user' => $id_user
    ]))->delete();
    static::id_regenerate('a');
  }

}}