<?php

namespace effectivecore\modules\user {
          use \effectivecore\html;
          use \effectivecore\url;
          use \effectivecore\message;
          class form_user_n_delete extends \effectivecore\html_form {

  public $form_id = 'user_n_delete';

  function __construct($attr = [], $content = null) {
    parent::__construct($attr, $content);
    $this->add_element(new html('input', ['type' => 'submit', 'name' => 'op', 'value' => 'Delete']));
    $this->add_element(new html('input', ['type' => 'submit', 'name' => 'op', 'value' => 'Cancel']));
  }

  function on_validate($args = []) {
  }

  function on_submit($args = []) {
    if (!empty($args['user_id']) &&
        !empty($args['op'])) {
      if ($args['op'] == 'Delete' && table_user::delete(['id' => $args['user_id']])) {
        message::set_before_redirect('User with id "'.$args['user_id'].'" was delited.');
        table_session::delete(['user_id' => $args['user_id']]);
      }
    # redirect in any case (on press button 'Cancel' or 'Delete')
      $back_url = url::$current->args('back', 'query');
      url::go($back_url ? urldecode($back_url) : '/admin/users');
    }
  }

}}