<?php

  ##################################################################
  ### Copyright © 2017—2019 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore\modules\user {
          use const \effcore\br;
          use \effcore\core;
          use \effcore\entity;
          use \effcore\file;
          use \effcore\instance;
          use \effcore\message;
          use \effcore\page;
          use \effcore\session;
          use \effcore\translation;
          use \effcore\url;
          use \effcore\user;
          abstract class events_form extends \effcore\events_form {

  ###################
  ### form: login ###
  ###################

  static function on_init_login($form, $items) {
    if (!isset($_COOKIE['cookies_is_on'])) {
      message::insert(
        translation::get('Cookies are disabled. You can not log in!').br.
        translation::get('Enable cookies before login.'), 'warning'
      );
    }
  }

  static function on_validate_login($form, $items) {
    switch ($form->clicked_button->value_get()) {
      case 'login':
        if ($form->total_errors_count_get() == 0) {
          $user = (new instance('user', [
            'email' => strtolower($items['#email']->value_get())
          ]))->select();
          if (!$user || (
               $user->password_hash &&
               $user->password_hash !== core::hash_password_get($items['#password']->value_get()))) {
            $items['#email'   ]->error_set();
            $items['#password']->error_set();
            $form->error_set('Incorrect email or password!');
          }
        }
        break;
    }
  }

  static function on_submit_login($form, $items) {
    switch ($form->clicked_button->value_get()) {
      case 'login':
        $user = (new instance('user', [
          'email' => strtolower($items['#email']->value_get())
        ]))->select();
        if ($user &&
            $user->password_hash === core::hash_password_get($items['#password']->value_get())) {
          session::insert($user->id,
            core::array_kmap($items['*session_params']->values_get())
          );
          url::go('/user/'.$user->id);
        }
        break;
    }
  }

  ##########################
  ### form: registration ###
  ##########################

  static function on_validate_registration($form, $items) {
    switch ($form->clicked_button->value_get()) {
      case 'register':
        if ($form->total_errors_count_get() == 0) {
        # test email
          if ((new instance('user', ['email' => strtolower($items['#email']->value_get())]))->select()) {
            $items['#email']->error_set(
              'User with this EMail was already registered!'
            );
            return;
          }
        # test nick
          if ((new instance('user', ['nick' => strtolower($items['#nick']->value_get())]))->select()) {
            $items['#nick']->error_set(
              'User with this Nick was already registered!'
            );
            return;
          }
        }
        break;
    }
  }

  static function on_submit_registration($form, $items) {
    switch ($form->clicked_button->value_get()) {
      case 'register':
        $user = (new instance('user', [
          'email'         => strtolower($items['#email']->value_get()),
          'nick'          => strtolower($items['#nick']->value_get()),
          'password_hash' => core::hash_password_get($items['#password']->value_get())
        ]))->insert();
        if ($user) {
          session::insert($user->id,
            core::array_kmap($items['*session_params']->values_get())
          );
          url::go('/user/'.$user->id);
        } else {
          message::insert('User was not registered!', 'error');
        }
        break;
    }
  }

  ####################
  ### form: logout ###
  ####################

  static function on_submit_logout($form, $items) {
    switch ($form->clicked_button->value_get()) {
      case 'logout':
        session::delete(user::current_get()->id);
        url::go('/');
        break;
      case 'cancel':
        url::go(url::back_url_get() ?: '/');
        break;
    }
  }

}}