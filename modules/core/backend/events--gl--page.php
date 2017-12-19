<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class events_page {

  static function on_show_block_logo($page) {
    return new markup('x-block', ['id' => 'logo'],
           new markup('a',       ['id' => 'home', 'href'  => '/', 'title' => translation::get('to home')]));
  }

  static function on_show_block_menu_user($page) {
    $user = user::get_current();
    if (empty($user->id)) {
      return new markup('x-block', ['id' => 'user_menu'], [
        storage::get('files')->select_by_npath('trees/user/user_anonymous'),
        new markup('img', ['id' => 'avatar', 'src' => '/modules/page/frontend/avatar-anonymous.svg'])
      ]);
    } else {
      return new markup('x-block', ['id' => 'user_menu'], [
        storage::get('files')->select_by_npath('trees/user/user_logged_in'),
        new markup('a', ['href' => '/user/'.$user->id],
          new markup('img', [
            'id' => 'avatar',
            'src' => $user->avatar_path_relative ?
                 '/'.$user->avatar_path_relative : '/modules/page/frontend/avatar-logged_in.svg']
        ))
      ]);
    }
  }

  static function on_show_block_title($page) {
    return new markup('h1', ['id' => 'title'],
      token::replace(translation::get($page->title))
    );
  }

  static function on_show_block_copyright($page) {
    return new markup('x-copyright', [], [
      translation::get('Valid HTML5 markup | Valid CSS').br.
      translation::get('Copyright © %%_years %%_right_holder.', ['years' => '2017—2018', 'right_holder' => 'Maxim Rysevets']).br.
      translation::get('All rights reserved.')
    ]);
  }

}}