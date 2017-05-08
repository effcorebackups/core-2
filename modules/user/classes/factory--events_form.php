<?php

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\entity_instance as entity_instance;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\modules\user\session_factory as session;
          use \effectivecore\modules\user\user_factory as user;
          use \effectivecore\modules\storage\db_factory as db;
          abstract class events_form_factory extends \effectivecore\events_form_factory {

  static function on_submit_user_n_delete($page_args, $form_args, $post_args) {
    $user_id  = $page_args['user_id'];
    switch ($post_args['button']) {
      case 'delete':
        $result = table_user::delete(['id' => $user_id]);
        if ($result) {
          table_session::delete(['user_id' => $user_id]);
          messages::add_new('User with id = '.$user_id.' was deleted.');
          urls::go(urls::get_back_part() ?: '/admin/users');
        } else {
          messages::add_new('User is not deleted!', 'error');
        }
        break;
      case 'cancel':
        urls::go(urls::get_back_part() ?: '/admin/users');
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
          urls::go(urls::get_back_part() ?: '/user/'.$user_id);
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
        $user = (new entity_instance('entities/user/user', ['email' => $email]))->select(['email']);
        if ($user &&
            $user->get_value('id') &&
            $user->get_value('password_hash') === $password_hash) {
          session::init($user->get_value('id'));
          urls::go('/user/'.$user->get_value('id'));
        } else {
          messages::add_new('Incorrect email or password!', 'error');
        }
        break;
    }
  }

  static function on_submit_user_register($page_args, $form_args, $post_args) {
    $email         = $post_args['email'];
    $password_hash = sha1($post_args['password']);
    $created       = date(format_datetime, time());
    switch ($post_args['button']) {
      case 'register':
        $is_exist = table_user::select_one(['id'], ['email' => $email]);
        if ($is_exist) {
          messages::add_new('User with this email is already exist!', 'error');
        } else {
          $new_id = table_user::insert(['email' => $email, 'password_hash' => $password_hash, 'created' => $created]);
          if ($new_id) {
            session::init($new_id);
            urls::go('/user/'.$new_id);
          } else {
            messages::add_new('User is not created!', 'error');
          }
        }
        break;
    }
  }

  static function on_submit_user_logout($page_args, $form_args, $post_args) {
    switch ($post_args['button']) {
      case 'logout':
        session::destroy(user::$current->id);
        urls::go('/');
    }
  }

}}