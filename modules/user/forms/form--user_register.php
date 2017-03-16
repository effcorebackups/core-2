<?php

namespace effectivecore\modules\user {
          use \effectivecore\html;
          use \effectivecore\url;
          use const \effectivecore\format_datetime;
          class form_user_register extends \effectivecore\html_form {

  public $form_id = 'user_register';

  function __construct($attr = [], $content = null) {
    parent::__construct($attr + ['novalidate' => true], $content);
    $this->add_element(new html('input', ['type' => 'email', 'name' => 'email', 'placeholder' => 'Email', 'required' => true, 'maxlength' => 255]));
    $this->add_element(new html('input', ['type' => 'password', 'name' => 'password', 'placeholder' => 'Password', 'required' => true, 'minlength' => 5]));
    $this->add_element(new html('input', ['type' => 'submit', 'name' => 'op', 'value' => 'Register']));
  }

  function on_validate($args = []) {
  }

  function on_submit($args = []) {
    if (table_user::select(['id'], ['email' => $args['email']]) == []) {
      $new_user_id = table_user::insert(['email' => $args['email'], 'password_hash' => sha1($args['password']), 'created' => date(format_datetime, time())]);
      session::init($new_user_id);
      url::go('/user/'.$new_user_id);
    } else {
      $this->errors['email'][] = 'This email is already registered!';
    }
  }

}}