<?php

namespace effectivecore\modules\user {
          use \effectivecore\entity_factory as entity_factory;
          use \effectivecore\entity_instance as entity_instance;
          abstract class user_factory {

  static $current;

  static function init($id = 0) {
    static::$current = new \StdClass();
    static::$current->id = 0;
    static::$current->roles = ['anonymous' => 'anonymous'];
 /* load user from db */
    if ($id) {
      $user = (new entity_instance('entities/user/user', ['id' => $id]))->select();
      if ($user) {
        static::$current = (object)($user->get_values());
        static::$current->roles = ['registered' => 'registered'];
        foreach (entity_factory::get('relation_role_ws_user')->select_set(['user_id' => $id]) as $c_role) {
          static::$current->roles[$c_role->role_id] = $c_role->role_id;
        }
      }
    }
  }

}}