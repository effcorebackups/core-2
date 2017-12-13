<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\user {
          use \effectivecore\entity as entity;
          use \effectivecore\instance as instance;
          abstract class user {

  static $data;

  static function init($id = null) {
    static::$data = new \stdClass();
    static::$data->id = 0;
    static::$data->roles = ['anonymous' => 'anonymous'];
  # load user from storage
    if ($id !== null) {
      $user = (new instance('user', [
        'id' => $id
      ]))->select();
      if ($user) {
        static::$data = (object)($user->get_values());
        static::$data->roles = ['registered' => 'registered'];
        foreach (entity::get('relation_role_ws_user')->select_instances(['id_user' => $user->id]) as $c_role) {
          static::$data->roles[$c_role->id_role] = $c_role->id_role;
        }
      }
    }
  }

  static function get_current() {
    if (!static::$data) static::init();
    return static::$data;
  }

}}