<?php

namespace effectivecore\modules\demo {
          use \effectivecore\message;
          abstract class events_form extends \effectivecore\events {

  static function on_submit_demo($page_args, $form_args, $post_args) {
    message::set('Your text is: '.$post_args['text']);
  }

}}