<?php

namespace effectivecore\modules\demo {
          use \effectivecore\message_factory as messages;
          abstract class events_form extends \effectivecore\events_form {

  static function on_init_demo($form, $elements) {
    messages::add_new('Call \effectivecore\modules\demo\events_form::on_init_demo.');
  }

  static function on_submit_demo($form, $elements) {
    messages::add_new('Call \effectivecore\modules\demo\events_form::on_submit_demo.');
  }

}}