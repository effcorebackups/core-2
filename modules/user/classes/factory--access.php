<?php

namespace effectivecore\modules\user {
          use \effectivecore\modules\user\user_factory as users;
          abstract class access_factory {

  static function check($access) {
    foreach (users::get_current()->roles as $c_role) {
      if (isset($access->roles[$c_role])) {
        return true;
      }
    }
  }

}}