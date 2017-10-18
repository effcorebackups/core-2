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

  #########################
  ### form: user_delete ###
  #########################

  static function on_submit_user_delete($form, $fields, &$values) {
    $id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'delete':
        $user = (new instance('user', [
          'id' => $id,
        ]))->select();
        if ($user) {
          $nick = $user->nick;
          if ($user->delete()) {
            $sessions = entities::get('session')->select_instance_set(['user_id' => $id]);
            if ($sessions) {
              foreach ($sessions as $c_session) {
                $c_session->delete();
              }
            }
               messages::add_new(translations::get('User %%_nick was deleted.',     ['nick' => $nick]));}
          else messages::add_new(translations::get('User %%_nick was not deleted!', ['nick' => $nick]), 'error');
        }
        urls::go(urls::get_back_url() ?: '/admin/users');
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/admin/users');
        break;
    }
  }

  #######################
  ### form: user_edit ###
  #######################

  static function on_init_user_edit($form, $fields) {
    $id = pages::$args['user_id'];
    $user = (new instance('user', ['id' => $id]))->select();
    $fields['credentials/email']->child_select('element')->attribute_insert('value', $user->email);
    $fields['credentials/nick']->child_select('element')->attribute_insert('value', $user->nick);
  }

  static function on_validate_user_edit($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
    if (!count($form->errors)) {
      $id = pages::$args['user_id'];
      switch ($form->clicked_button_name) {
        case 'save':
          $user = (new instance('user', ['id' => $id]))->select();
          if ($user->password_hash !== sha1($values['password_old'])) {
            $form->add_error('credentials/password_old/element',
              'Old password is incorrect!'
            );
            return;
          }
          if ($values['password_new'] ==
              $values['password_old']) {
            $form->add_error('credentials/password_new/element',
              'New password must be different from the old password!'
            );
            return;
          }
      }
    }
  }

  static function on_submit_user_edit($form, $fields, &$values) {
    $id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'save':
        $user = (new instance('user', ['id' => $id]))->select();
        $user->password_hash = sha1($values['password_new']);
        if ($user->update()) {
          messages::add_new(
            translations::get('User %%_nick was updated.', ['nick' => $user->nick])
          );
          urls::go(urls::get_back_url() ?: '/user/'.$id);
        } else {
          messages::add_new(
            translations::get('User %%_nick was not updated.', ['nick' => $user->nick]), 'error'
          );
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/user/'.$id);
        break;
    }
  }

  ###################
  ### form: login ###
  ###################

  static function on_validate_login($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
    switch ($form->clicked_button_name) {
      case 'login':
        if (count($form->errors) == 0) {
          $user = (new instance('user', [
            'email' => $values['email']
          ]))->select();
          if (!$user || (
               $user->password_hash &&
               $user->password_hash !== sha1($values['password']))) {
            $form->add_error('credentials/email/element');
            $form->add_error('credentials/password/element');
            messages::add_new('Incorrect email or password!', 'error');
          }
        }
        break;
    }
  }

  static function on_submit_login($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'login':
        $user = (new instance('user', [
          'email' => $values['email']
        ]))->select();
        if ($user     &&
            $user->id &&
            $user->password_hash === sha1($values['password'])) {
          session::init($user->id);
          urls::go('/user/'.$user->id);
        }
        break;
    }
  }

  ##########################
  ### form: registration ###
  ##########################

  static function on_validate_registration($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
    switch ($form->clicked_button_name) {
      case 'register':
        if (count($form->errors) == 0) {
        # test email
          if ((new instance('user', ['email' => $values['email']]))->select()) {
            $form->add_error('credentials/email/element', 'User with this EMail was already registered!');
            return;
          }
        # test nick
          if ((new instance('user', ['nick' => $values['nick']]))->select()) {
            $form->add_error('credentials/nick/element', 'User with this Nick was already registered!');
            return;
          }
        }
        break;
    }
  }

  static function on_submit_registration($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'register':
        $user = (new instance('user', [
          'email'         => $values['email'],
          'nick'          => $values['nick'],
          'password_hash' => sha1($values['password']),
          'created'       => factory::datetime_get_curent()
        ]))->insert();
        if ($user) {
          session::init($user->id);
          urls::go('/user/'.$user->id);
        } else {
          messages::add_new('User was not registered!', 'error');
        }
        break;
    }
  }

  ####################
  ### form: logout ###
  ####################

  static function on_submit_logout($form, $fields, &$values) {
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