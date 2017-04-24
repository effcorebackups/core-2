<?php

namespace effectivecore\modules\page {
          use \effectivecore\messages_factory as messages;
          use \effectivecore\modules\page\page_factory as page;
          abstract class events_module_factory extends \effectivecore\events_module_factory {

  static function on_init() {
    messages::init();
    page::init();
  }

}}