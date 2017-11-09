<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          use \effectivecore\factory as factory;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\instance as instance;
          use \effectivecore\entity_factory as entity;
          use \effectivecore\message_factory as message;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\page\pages_factory as pages;
          use \effectivecore\modules\user\users_factory as users;
          use \effectivecore\modules\user\session_factory as session;
          abstract class events_form extends \effectivecore\events_form {

  #########################
  ### form: user_delete ###
  #########################

  static function on_submit_user_delete($form, $fields, &$values) {
    $id = pages::$args['id_user'];
    switch ($form->clicked_button_name) {
      case 'delete':
        $user = (new instance('user', [
          'id' => $id,
        ]))->select();
        if ($user) {
          $nick = $user->nick;
          if ($user->delete()) {
            $sessions = entity::get('session')->select_instances(['id_user' => $id]);
            if ($sessions) {
              foreach ($sessions as $c_session) {
                $c_session->delete();
              }
            }
               message::add_new(translations::get('User %%_nick was deleted.',     ['nick' => $nick]));}
          else message::add_new(translations::get('User %%_nick was not deleted!', ['nick' => $nick]), 'error');
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
    $id = pages::$args['id_user'];
    $user = (new instance('user', ['id' => $id]))->select();
    $fields['credentials/email']->child_select('element')->attribute_insert('value', $user->email);
    $fields['credentials/nick']->child_select('element')->attribute_insert('value', $user->nick);
  }

  static function on_validate_user_edit($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
    switch ($form->clicked_button_name) {
      case 'save':
        if (count($form->errors) == 0) {
          $id = pages::$args['id_user'];
        # check security
          $test_pass = (new instance('user', ['id' => $id]))->select();
          if ($test_pass->password_hash !== factory::hash_get($values['password'])) {
            $form->add_error('credentials/password/element',
              translations::get('Field "%%_title" contains incorrect value!', [
                'title' => translations::get($fields['credentials/password']->title)
              ])
            );
            return;
          }
        # test email
          $test_email = (new instance('user', ['email' => $values['email']]))->select();
          if ($test_email &&
              $test_email->id != $id) {
            $form->add_error('credentials/email/element', 'User with this EMail was already registered!');
            return;
          }
        # test nick
          $test_nick = (new instance('user', ['nick' => $values['nick']]))->select();
          if ($test_nick &&
              $test_nick->id != $id) {
            $form->add_error('credentials/nick/element', 'User with this Nick was already registered!');
            return;
          }
        # test new password
          if ($values['password'] ==
              $values['password_new']) {
            $form->add_error('credentials/password_new/element',
              'New password must be different from the current password!'
            );
            return;
          }
        }
        break;
    }
  }

  static function on_submit_user_edit($form, $fields, &$values) {
    $id = pages::$args['id_user'];
    switch ($form->clicked_button_name) {
      case 'save':
        $user = (new instance('user', ['id' => $id]))->select();
        $user->email = $values['email'];
        $user->nick  = $values['nick'];
        if ($values['password_new']) {
          $user->password_hash = factory::hash_get($values['password_new']);
        }
        if ($user->update()) {
          message::add_new(
            translations::get('User %%_nick was updated.', ['nick' => $user->nick])
          );
          urls::go(urls::get_back_url() ?: '/user/'.$id);
        } else {
          message::add_new(
            translations::get('User %%_nick was not updated.', ['nick' => $user->nick]), 'warning'
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
               $user->password_hash !== factory::hash_get($values['password']))) {
            $form->add_error('credentials/email/element');
            $form->add_error('credentials/password/element');
            message::add_new('Incorrect email or password!', 'error');
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
            $user->password_hash === factory::hash_get($values['password'])) {
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
          'password_hash' => factory::hash_get($values['password']),
          'created'       => factory::datetime_get_curent()
        ]))->insert();
        if ($user) {
          session::init($user->id);
          urls::go('/user/'.$user->id);
        } else {
          message::add_new('User was not registered!', 'error');
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