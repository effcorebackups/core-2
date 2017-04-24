<?php

namespace effectivecore\modules\page {
          use \effectivecore\messages_factory;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    messages_factory::init();
    page::init();
  }

}}