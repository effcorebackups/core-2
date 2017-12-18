<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class session {

  static function id_regenerate($type, $expire = 60 * 60 * 24 * 30) {
    $hex_type = $type; # 'a' - anonymous user | 'f' - authenticated user
    $hex_expire = dechex(time() + $expire);
    $hex_ip = factory::ip_to_hex($_SERVER['REMOTE_ADDR']);
    $hex_uagent_hash_8 = substr(md5($_SERVER['HTTP_USER_AGENT']), 0, 8);
    $hex_random = str_pad(dechex(rand(0, 0xffffffff)), 8, '0', STR_PAD_LEFT);
    $session_id = $hex_type.          # strlen == 1
                  $hex_expire.        # strlen == 8
                  $hex_ip.            # strlen == 8
                  $hex_uagent_hash_8. # strlen == 8
                  $hex_random;        # strlen == 8
    setcookie('session_id', ($_COOKIE['session_id'] = $session_id), $expire ? time() + $expire : 0, '/');
    setcookie('cookies_is_on', 'true', $expire ? time() + $expire : 0, '/');
    return $session_id;
  }

  static function id_get() {
    $c_value = factory::filter_session_id(
      isset($_COOKIE['session_id']) ?
            $_COOKIE['session_id'] : '');
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