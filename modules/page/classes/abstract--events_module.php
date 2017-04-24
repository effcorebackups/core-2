<?php

namespace effectivecore\modules\page {
          use \effectivecore\messages;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    messages::init();
    page::init();
  }

}}