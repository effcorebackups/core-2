<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\factory as factory;
          use \effectivecore\instance as instance;
          use \effectivecore\modules\user\user as user;
          abstract class events_access extends \effectivecore\events_access {

  static function on_check_access_user_delete($page, $id) {
    $user = (new instance('user', ['id' => $id]))->select();
    if ($user) {
      if ($user->is_embed == 1) {
        factory::send_header_and_exit('access_denided',
          'This user is embed!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_check_access_user_edit($page, $id) {
    $user = (new instance('user', ['id' => $id]))->select();
    if ($user) {
      if (!($user->id == user::get_current()->id ||                # not owner or
                   isset(user::get_current()->roles['admins']))) { # not admin
        factory::send_header_and_exit('access_denided',
          'Access denided!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

}}