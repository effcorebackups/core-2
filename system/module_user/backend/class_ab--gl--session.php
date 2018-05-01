<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          abstract class session {

  const period_expire = 60 * 60 * 24 * 30;
  const period_seance = 60 * 60 * 24;
  const empty_ip = '0.0.0.0';

  static function select() {
    $session_id = static::id_get();
    if ($session_id[0] == 'f') {
      $session = (new instance('session', [
        'id' => $session_id
      ]))->select();
      if (!$session) {
        static::id_regenerate('a');
        message::insert('invalid session was deleted!', 'warning');
        return null;
      } else {
        return $session;
      }
    }
  }

  static function insert($id_user, $session_params = []) {
    $is_remember = isset($session_params['remember']);
    $is_fixed_ip = isset($session_params['fixed_ip']);
    $period = !$is_remember ? static::period_seance : static::period_expire;
    static::id_regenerate('f', $session_params);
    (new instance('session', [
      'id'       => static::id_get(),
      'id_user'  => $id_user,
      'remember' => $is_remember ? 1 : 0,
      'fixed_ip' => $is_fixed_ip ? 1 : 0,
      'expire'   => factory::datetime_get('+'.$period.' second'),
    ]))->insert();
  }

  static function delete($id_user) {
    (new instance('session', [
      'id'      => static::id_get(),
      'id_user' => $id_user
    ]))->delete();
    static::id_regenerate('a');
  }

  ############################
  ### session_id functions ###
  ############################

  # anonymous | remember? | on ip? | session id  | do not track?  | secure?  | use
  # ──────────────────────────────────────────────────────────────────────────────
  # yes       | no        | no     | a--01--00-- | yes            | no       | -
  # yes       | no        | yes    | a--01--ip-- | no             | no       | -
  # yes       | yes       | no     | a--30--00-- | no             | no       | -
  # yes       | yes       | yes    | a--30--ip-- | no             | on https | +
  # ──────────────────────────────────────────────────────────────────────────────
  # no        | no        | no     | f--01--00-- | no - logged in | no       | +
  # no        | no        | yes    | f--01--ip-- | no - logged in | no       | +
  # no        | yes       | no     | f--30--00-- | no - logged in | no       | +
  # no        | yes       | yes    | f--30--ip-- | no - logged in | on https | +
  # ──────────────────────────────────────────────────────────────────────────────

  static function id_regenerate($type, $session_params = []) {
    $is_remember = isset($session_params['remember']);
    $is_fixed_ip = isset($session_params['fixed_ip']);
    $period = $type == 'f' && !$is_remember ? static::period_seance : static::period_expire;
    $ip     = $type == 'f' && !$is_fixed_ip ? static::empty_ip : $_SERVER['REMOTE_ADDR'];
    $hex_type = $type; # 'a' - anonymous user | 'f' - authenticated user
    $hex_expire = dechex(time() + $period);
    $hex_ip = factory::ip_to_hex($ip);
    $hex_uagent_hash_8 = substr(md5($_SERVER['HTTP_USER_AGENT']), 0, 8);
    $hex_random = str_pad(dechex(rand(0, 0xffffffff)), 8, '0', STR_PAD_LEFT);
    $session_id = $hex_type.          # strlen == 1
                  $hex_expire.        # strlen == 8
                  $hex_ip.            # strlen == 8
                  $hex_uagent_hash_8. # strlen == 8
                  $hex_random;        # strlen == 8
    $session_id.= factory::signature_get($session_id, 8);
    setcookie('session_id', ($_COOKIE['session_id'] = $session_id), time() + $period, '/');
    setcookie('cookies_is_on', 'true',                              time() + $period, '/');
    return $session_id;
  }

  static function id_get() {
    if (static::id_check(
          isset($_COOKIE['session_id']) ?
                $_COOKIE['session_id'] : '')) {
      return    $_COOKIE['session_id']; } else {
      return static::id_regenerate('a');
    }
  }

  static function id_decode_type($id)          {return substr($id, 0, 1);}
  static function id_decode_expire($id)        {return hexdec(substr($id, 1, 8));}
  static function id_decode_ip($id)            {return factory::hex_to_ip(substr($id, 8 + 1, 8));}
  static function id_decode_uagent_hash_8($id) {return substr($id, 16 + 1, 8);}
  static function id_decode_random($id)        {return hexdec(substr($id, 24 + 1, 8));}
  static function id_decode_signature($id)     {return substr($id, 32 + 1, 8);}

  static function id_check($value) {
    if (factory::filter_hash($value, 41)) {
      $type          = static::id_decode_type($value);
      $expire        = static::id_decode_expire($value);
      $ip            = static::id_decode_ip($value);
      $uagent_hash_8 = static::id_decode_uagent_hash_8($value);
      $random        = static::id_decode_random($value);
      $signature     = static::id_decode_signature($value);
      if ($expire >= time() &&
          $uagent_hash_8 === substr(md5($_SERVER['HTTP_USER_AGENT']), 0, 8) &&
          $signature === factory::signature_get(substr($value, 0, 33), 8)) {
        if (($type === 'a' && $ip === $_SERVER['REMOTE_ADDR']) ||
            ($type === 'f' && $ip === $_SERVER['REMOTE_ADDR']) ||
            ($type === 'f' && $ip === static::empty_ip)) {
          return true;
        }
      }
    }
  }

}}