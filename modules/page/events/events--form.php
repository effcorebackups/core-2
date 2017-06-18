<?php

namespace effectivecore\modules\page {
          use \effectivecore\message_factory as messages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_submit_admin_decoration($form, $elements) {
    messages::add_new('Test');
  }

}}