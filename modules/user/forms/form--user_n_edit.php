<?php

namespace effectivecore\modules\user {
          use \effectivecore\html;
          use \effectivecore\message;
          use \effectivecore\url;
          class form_user_n_edit extends \effectivecore\html_form {

  public $form_id = 'user_n_edit';

  function __construct($attr = [], $content = null) {
    parent::__construct($attr + ['novalidate' => true], $content);
    $this->add_element(new html('input', ['type' => 'password', 'name' => 'password', 'placeholder' => 'Password', 'required' => true, 'minlength' => 5]));
    $this->add_element(new html('input', ['type' => 'submit', 'name' => 'op', 'value' => 'Save']));
  }

  function on_validate($args = []) {
  }

  function on_submit($args = []) {
    if (table_user::update(['password_hash' => sha1($args['password'])], ['id' => $args['user_id']])) {
      message::set_before_redirect('Parameters of user with id = '.$args['user_id'].' was updated.');
    }
  # redirect to back
    $back_url = url::$current->args('back', 'query');
    url::go($back_url ? urldecode($back_url) : '/user/'.$args['user_id']);
  }

}}