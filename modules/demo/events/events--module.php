<?php

namespace effectivecore\modules\demo {
          use \effectivecore\message_factory as messages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    messages::add_new('Call \effectivecore\modules\demo\events_module::on_start.');
  }

}}