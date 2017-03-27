<?php

namespace effectivecore\modules\user {
          use \effectivecore\urls;
          use \effectivecore\messages;
          use \effectivecore\modules\data\db;
          use const \effectivecore\format_datetime;
          abstract class events_form extends \effectivecore\events_form {

  function on_submit_user_n_delete($page_args, $form_args, $post_args) {
    $back_url = urldecode(urls::$current->get_args('back', 'query'));
    $user_id  = $page_args['user_id'];
    switch ($post_args['button']) {
      case 'delete':
        $result = table_user::delete(['id' => $user_id]);
        if ($result) {
          table_session::delete(['user_id' => $user_id]);
          messages::add_new('User with id = '.$user_id.' was deleted.');
          urls::go($back_url ?: '/admin/users');
        } else {
          messages::add_new('User is not deleted!', 'error');
        }
        break;
      case 'cancel':
        urls::go($back_url ?: '/admin/users');
        break;
    }
  }

  function on_submit_user_n_edit($page_args, $form_args, $post_args) {
    $back_url      = urldecode(urls::$current->get_args('back', 'query'));
    $user_id       = $page_args['user_id'];
    $password_hash = sha1($post_args['password']);
    switch ($post_args['button']) {
      case 'save':
        $result = table_user::update(['password_hash' => $password_hash], ['id' => $user_id]);
        if ($result) {
          messages::add_new('Parameters of user with id = '.$user_id.' was updated.');
          urls::go($back_url ?: '/user/'.$user_id);
        } else {
          messages::add_new('Parameters is not updated!', 'error');
        }
        break;
    }
  }

  function on_submit_user_login($page_args, $form_args, $post_args) {
    $email         = $post_args['email'];
    $password_hash = sha1($post_args['password']);
    switch ($post_args['button']) {
      case 'login':
        $result = table_user::select_one(['id'], ['password_hash' => $password_hash, 'email' => $email]);
        if (isset($result['id'])) {
          session::init($result['id']);
          urls::go('/user/'.$result['id']);
        } else {
          messages::add_new('Incorrect email or password!', 'error');
        }
        break;
    }
  }

  function on_submit_user_register($page_args, $form_args, $post_args) {
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

}}