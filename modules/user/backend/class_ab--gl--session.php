<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          const session_id_period = 60 * 60 * 24 * 30;
          abstract class session {

  static function select() {
    $session_id = static::id_get();
    if ($session_id[0] == 'f') {
      $session = (new instance('session', [
        'id' => $session_id
      ]))->select();
      if (!$session) {
        static::id_regenerate('a', session_id_period);
        message::insert('invalid session was deleted!', 'warning');
        return null;
      } else {
        return $session;
      }
    }
  }

  static function insert($id_user, $remember_mode) {
    if ($remember_mode == 0)
         static::id_regenerate('f', 0);
    else static::id_regenerate('f', session_id_period);
    (new instance('session', [
      'id'            => static::id_get(),
      'id_user'       => $id_user,
      'remember_mode' => $remember_mode,
      'expire'        => factory::datetime_get('+'.session_id_period.' second'),
    ]))->insert();
  }

  static function delete($id_user) {
    (new instance('session', [
      'id'      => static::id_get(),
      'id_user' => $id_user
    ]))->delete();
    static::id_regenerate('a', session_id_period);
  }

  ############################
  ### session_id functions ###
  ############################

  static function id_regenerate($type, $period) {
    $hex_type = $type; # 'a' - anonymous user | 'f' - authenticated user
    $hex_expire = dechex(time() + session_id_period);
    $hex_ip = factory::ip_to_hex($_SERVER['REMOTE_ADDR']);
    $hex_uagent_hash_8 = substr(md5($_SERVER['HTTP_USER_AGENT']), 0, 8);
    $hex_random = str_pad(dechex(rand(0, 0xffffffff)), 8, '0', STR_PAD_LEFT);
    $session_id = $hex_type.          # strlen == 1
                  $hex_expire.        # strlen == 8
                  $hex_ip.            # strlen == 8
                  $hex_uagent_hash_8. # strlen == 8
                  $hex_random;        # strlen == 8
    $session_id.= factory::signature_get($session_id, 8);
    setcookie('session_id', ($_COOKIE['session_id'] = $session_id), $period ? time() + $period : 0, '/');
    setcookie('cookies_is_on', 'true',                              $period ? time() + $period : 0, '/');
    return $session_id;
  }

  static function id_get() {
    if (static::id_check(
          isset($_COOKIE['session_id']) ?
                $_COOKIE['session_id'] : '')) {
      return    $_COOKIE['session_id']; } else {
      return static::id_regenerate('a', session_id_period);
    }
  }

  static function id_decode_type($id)   {return substr($id, 0, 1);}
  static function id_decode_expire($id) {return hexdec(substr($id, 1, 8));}
  static function id_decode_ip($id)     {return factory::hex_to_ip(substr($id, 8 + 1, 8));}

  static function id_check($value) {
    if (factory::filter_hash($value, 41)) {
      $type = static::id_decode_type($value);
      $expire = static::id_decode_expire($value);
      $ip = static::id_decode_ip($value);
      $uagent_hash_8 = substr($value, 16 + 1, 8);
      $random = hexdec(substr($value, 24 + 1, 8));
      $signature = substr($value, 32 + 1, 8);
      if ($expire >= time()                     &&
          $expire <= time() + session_id_period &&
          $ip === $_SERVER['REMOTE_ADDR']       &&
          $uagent_hash_8 === substr(md5($_SERVER['HTTP_USER_AGENT']), 0, 8) &&
          $signature === factory::signature_get(substr($value, 0, 33), 8)) {
        return true;
      }
    }
  }

}}