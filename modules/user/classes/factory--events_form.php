<?php

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          use \effectivecore\url_factory as urls;
          use \effectivecore\entity_factory as entity_factory;
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\message_factory as messages;
          use \effectivecore\modules\user\session_factory as session;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_form_factory extends \effectivecore\events_form_factory {

  static function on_submit_user_n_delete($page_args, $form_args, $post_args) {
    $user_id  = $page_args['user_id'];
    switch ($post_args['button']) {
      case 'delete':
        $result = (new entity_instance('entities/user/user', [
          'id' => $user_id,
        ]))->delete();
        if ($result) {
          $session_set = entity_factory::get('session')->select_set(['user_id' => $user_id]);
          if ($session_set) {
            foreach ($session_set as $c_session) {
              $c_session->delete();
            }
          }
          messages::add_new('User with id = '.$user_id.' was deleted.');
          urls::go(urls::get_back_url() ?: '/admin/users');
        } else {
          messages::add_new('User was not deleted!', 'error');
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/admin/users');
        break;
    }
  }

  static function on_submit_user_n_edit($page_args, $form_args, $post_args) {
    $user_id       = $page_args['user_id'];
    $password_hash = sha1($post_args['password']);
    switch ($post_args['button']) {
      case 'save':
        $result = (new entity_instance('entities/user/user', [
          'id'            => $user_id,
          'password_hash' => $password_hash,
        ]))->update();
        if ($result) {
          messages::add_new('Parameters of user with id = '.$user_id.' was updated.');
          urls::go(urls::get_back_url() ?: '/user/'.$user_id);
        } else {
          messages::add_new('Parameters is not updated!', 'error');
        }
        break;
    }
  }

  static function on_submit_user_login($page_args, $form_args, $post_args) {
    $email         = $post_args['email'];
    $password_hash = sha1($post_args['password']);
    switch ($post_args['button']) {
      case 'login':
        $user = (new entity_instance('entities/user/user', [
          'email' => $email
        ]))->select(['email']);
        if ($user &&
            $user->id &&
            $user->password_hash === $password_hash) {
          session::init($user->id);
          urls::go('/user/'.$user->id);
        } else {
          messages::add_new('Incorrect email or password!', 'error');
        }
        break;
    }
  }

  static function on_submit_user_register($page_args, $form_args, $post_args) {
    $email         = $post_args['email'];
    $password_hash = sha1($post_args['password']);
    switch ($post_args['button']) {
      case 'register':
        $user = (new entity_instance('entities/user/user', [
          'email' => $email
        ]))->select(['email']);
        if ($user) {
          messages::add_new('User with this email is already exist!', 'error');
        } else {
          $user = (new entity_instance('entities/user/user', [
            'email'         => $email,
            'password_hash' => $password_hash,
            'created'       => date(format_datetime, time())
          ]))->insert();
          if ($user->id) {
            session::init($user->id);
            urls::go('/user/'.$user->id);
          } else {
            messages::add_new('User was not created!', 'error');
          }
        }
        break;
    }
  }

  static function on_submit_user_logout($page_args, $form_args, $post_args) {
    switch ($post_args['button']) {
      case 'logout':
        session::destroy(user::get_current()->id);
        urls::go('/');
    }
  }

}}