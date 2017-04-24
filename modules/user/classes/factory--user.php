<?php

namespace effectivecore\modules\user {
          abstract class user_factory {

  static $current;

  static function init($id = 0) {
    static::$current = new \StdClass();
    static::$current->id = 0;
    static::$current->roles = ['anonymous' => 'anonymous'];
 /* load user from db */
    if ($id) {
      $db_user = table_user::select_one(['*'], ['id' => $id]);
      $db_user_roles = table_role_by_user::select(['role_id'], ['user_id' => $id]);
      if (isset($db_user['id'])) {
        static::$current = (object)$db_user;
        static::$current->roles = ['registered' => 'registered'];
        foreach ($db_user_roles as $c_role) {
          static::$current->roles[$c_role['role_id']] = $c_role['role_id'];
        }
      }
    }
  }

}}