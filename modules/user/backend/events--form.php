<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          use \effectivecore\factory as factory;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\instance as instance;
          use \effectivecore\entities_factory as entities;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\page\pages_factory as pages;
          use \effectivecore\modules\user\users_factory as users;
          use \effectivecore\modules\user\session_factory as session;
          abstract class events_form extends \effectivecore\events_form {

  static function on_submit_user_n_delete($form, $fields, &$values) {
    $user_id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'delete':
        $result = (new instance('user', [
          'id' => $user_id,
        ]))->delete();
        if ($result) {
          $session_set = entities::get('session')->select_instance_set(['user_id' => $user_id]);
          if ($session_set) {
            foreach ($session_set as $c_session) {
              $c_session->delete();
            }
          }
          messages::add_new(
            translations::get('User with ID = %%_id was deleted.', ['id' => $user_id])
          );
          urls::go(urls::get_back_url() ?: '/admin/users');
        } else {
          messages::add_new(
            translations::get('User was not deleted!'), 'error'
          );
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/admin/users');
        break;
    }
  }

  static function on_submit_user_n_edit($form, $fields, &$values) {
    $user_id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'save':
        $result = (new instance('user', [
          'id'            => $user_id,
          'password_hash' => sha1($values['password_new']),
        ]))->update();
        if ($result) {
          messages::add_new(
            translations::get('Data of user with ID = %%_id was updated.', ['id' => $user_id])
          );
          urls::go(urls::get_back_url() ?: '/user/'.$user_id);
        } else {
          messages::add_new(
            translations::get('Data was not updated!'), 'error'
          );
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/user/'.$user_id);
        break;
    }
  }

  static function on_submit_user_login($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'login':
        $user = (new instance('user', [
          'email' => $values['email']
        ]))->select(['email']);
        if ($user &&
            $user->id &&
            $user->password_hash === sha1($values['password'])) {
          session::init($user->id);
          urls::go('/user/'.$user->id);
        } else {
          messages::add_new(
            translations::get('Incorrect email or password!'), 'error'
          );
        }
        break;
    }
  }

  static function on_submit_user_register($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'register':
        $user = (new instance('user', [
          'email' => $values['email']
        ]))->select(['email']);
        if ($user) {
          messages::add_new(
            translations::get('User with this email was already registered!'), 'error'
          );
        } else {
          $user = (new instance('user', [
            'email'         => $values['email'],
            'password_hash' => sha1($values['password']),
            'created'       => factory::datetime_get_curent()
          ]))->insert();
          if ($user->id) {
            session::init($user->id);
            urls::go('/user/'.$user->id);
          } else {
            messages::add_new(
              translations::get('User was not registered!'), 'error'
            );
          }
        }
        break;
    }
  }

  static function on_submit_user_logout($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'logout':
        session::destroy(users::get_current()->id);
        urls::go('/');
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/');
        break;
    }
  }

}}