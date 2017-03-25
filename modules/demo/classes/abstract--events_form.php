<?php

namespace effectivecore\modules\demo {
          use \effectivecore\__message;
          abstract class events_form extends \effectivecore\events {

  static function on_submit_demo($page_args, $form_args, $post_args) {
    __message::set('Your text is: '.$post_args['text']);
  }

}}