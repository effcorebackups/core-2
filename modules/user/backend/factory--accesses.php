<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\modules\user\users_factory as users;
          abstract class accesses_factory {

  static function check($access) {
    foreach (users::get_current()->roles as $c_role) {
      if (isset($access->roles[$c_role])) {
        return true;
      }
    }
  }

}}