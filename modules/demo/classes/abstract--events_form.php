<?php

namespace effectivecore\modules\demo {
          use \effectivecore\messages;
          abstract class events_form extends \effectivecore\events {

  static function on_submit_demo($page_args, $form_args, $post_args) {
    messages::set('Your text is: '.$post_args['text']);
  }

}}