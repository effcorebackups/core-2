<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translation as translation;
          use \effectivecore\modules\user\user as user;
          use \effectivecore\modules\storage\storage as storage;
          abstract class events_page {

  static function on_show_block_logo($page) {
    return new markup('x-block', ['id' => 'logo'],
           new markup('a',       ['id' => 'home', 'href'  => '/', 'title' => translation::get('to home')]));
  }

  static function on_show_block_menu_user($page) {
    $user = user::get_current();
    if (empty($user->id)) {
      return new markup('x-block', ['id' => 'user_menu'], [
        storage::get('settings')->select_by_npath('trees/user/user_anonymous'),
        new markup('img', ['id' => 'avatar', 'src' => '/modules/page/frontend/avatar-anonymous.svg'])
      ]);
    } else {
      return new markup('x-block', ['id' => 'user_menu'], [
        storage::get('settings')->select_by_npath('trees/user/user_logged_in'),
        new markup('a', ['href' => '/user/'.$user->id], new markup('img', ['id' => 'avatar', 'src' => '/modules/page/frontend/avatar-logged_in.svg']))
      ]);
    }
  }

  static function on_show_block_title($page) {
    return new markup('h1', ['id' => 'title'],
      token::replace(translation::get($page->title))
    );
  }

}}