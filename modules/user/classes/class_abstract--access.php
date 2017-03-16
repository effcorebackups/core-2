<?php

namespace effectivecore\modules\user {
          abstract class access {

  static function check($access) {
    foreach (user::$current->roles as $c_role) {
      if (isset($access->roles[$c_role])) {
        return true;
      }
    }
  }

}}