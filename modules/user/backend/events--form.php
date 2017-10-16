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

  ###########################
  ### form: user_n_delete ###
  ###########################

  static function on_submit_user_n_delete($form, $fields, &$values) {
    $id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'delete':
        $result = (new instance('user', [
          'id' => $id,
        ]))->delete();
        if ($result) {
          $session_set = entities::get('session')->select_instance_set(['user_id' => $id]);
          if ($session_set) {
            foreach ($session_set as $c_session) {
              $c_session->delete();
            }
          }
          messages::add_new(
            translations::get('User with ID = %%_id was deleted.', ['id' => $id])
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

  #########################
  ### form: user_n_edit ###
  #########################

  static function on_validate_user_n_edit($form, $fields, &$values) {
    if (!count($form->errors)) {
      $id = pages::$args['user_id'];
      switch ($form->clicked_button_name) {
        case 'save':
          $user = (new instance('user', ['id' => $id]))->select();
          if ($user->password_hash !== sha1($values['password_old'])) {
            $form->add_error('fieldset_default/field_password_old/default',
              translations::get('Old password is incorrect!')
            );
            return;
          }
          if ($values['password_new'] ==
              $values['password_old']) {
            $form->add_error('fieldset_default/field_password_new/default',
              translations::get('The new password must be different from the old password!')
            );
            return;
          }
      }
    }
  }

  static function on_submit_user_n_edit($form, $fields, &$values) {
    $id = pages::$args['user_id'];
    switch ($form->clicked_button_name) {
      case 'save':
        $user = (new instance('user', ['id' => $id]))->select();
        $user->password_hash = sha1($values['password_new']);
        if ($user->update()) {
          messages::add_new(
            translations::get('Data of user with ID = %%_id was updated.', ['id' => $id])
          );
          urls::go(urls::get_back_url() ?: '/user/'.$id);
        } else {
          messages::add_new(
            translations::get('Data was not updated!'), 'error'
          );
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/user/'.$id);
        break;
    }
  }

  ########################
  ### form: user_login ###
  ########################

  static function on_validate_user_login($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'login':
        if (count($form->errors) == 0) {
          $user = (new instance('user', [
            'email' => $values['email']
          ]))->select();
          if ($user     &&
              $user->id &&
              $user->password_hash !== sha1($values['password'])) {
            $form->add_error('credentials/email/default');
            $form->add_error('credentials/password/default');
            messages::add_new(translations::get('Incorrect email or password!'), 'error');
          }
        }
        break;
    }
  }

  static function on_submit_user_login($form, $fields, &$values) {
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

  ###########################
  ### form: user_register ###
  ###########################

  static function on_submit_user_register($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'register':
        $user = (new instance('user', [
          'email' => $values['email']
        ]))->select();
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
          if ($user &&
              $user->id) {
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

  #########################
  ### form: user_logout ###
  #########################

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