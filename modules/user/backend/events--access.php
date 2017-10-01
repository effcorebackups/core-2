<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\factory as factory;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\entities_factory as entities;
          use \effectivecore\modules\user\users_factory as users;
          abstract class events_access extends \effectivecore\events_access {

  static function on_check_access_user_n_delete($user_id) {
    $user = (new entity_instance('user', ['id' => $user_id]))->select();
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

  static function on_check_access_user_n_edit($user_id) {
    $user = (new entity_instance('user', ['id' => $user_id]))->select();
    if ($user) {
      if (!($user->id == users::get_current()->id ||                # not owner or
                   isset(users::get_current()->roles['admins']))) { # not admin
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