<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\modules\user\user_factory as user;
          abstract class access_factory {

  static function check($access) {
    foreach (user::select_current()->roles as $c_role) {
      if (isset($access->roles[$c_role])) {
        return true;
      }
    }
  }

}}