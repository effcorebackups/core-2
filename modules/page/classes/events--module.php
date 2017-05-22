<?php

namespace effectivecore\modules\page {
          use \effectivecore\modules\page\page_factory as page;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    return page::render();
  }

}}