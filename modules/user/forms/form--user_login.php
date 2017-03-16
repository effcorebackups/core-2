<?php

namespace effectivecore\modules\user {
          use \effectivecore\html;
          use \effectivecore\url;
          class form_user_login extends \effectivecore\html_form {

  public $form_id = 'user_login';

  function __construct($attr = [], $content = null) {
    parent::__construct($attr + ['novalidate' => true], $content);
    $this->add_element(new html('input', ['type' => 'email', 'name' => 'email', 'placeholder' => 'Email', 'required' => true, 'maxlength' => 255]));
    $this->add_element(new html('input', ['type' => 'password', 'name' => 'password', 'placeholder' => 'Password', 'required' => true, 'minlength' => 5]));
    $this->add_element(new html('input', ['type' => 'submit', 'name' => 'op', 'value' => 'Login']));
  }

  function on_validate($args = []) {
  }

  function on_submit($args = []) {
    $user_info = table_user::select_first(['*'], ['email' => $args['email'], 'password_hash' => sha1($args['password'])]);
    if (!empty($user_info['id'])) {
      session::init($user_info['id']);
      url::go('/user/'.$user_info['id']);
    } else {
      $this->errors['email'][] = 'Incorrect email or password!';
    }
  }

}}