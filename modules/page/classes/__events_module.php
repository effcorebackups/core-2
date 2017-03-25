<?php

namespace effectivecore\modules\page {
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    page::init();
  }

}}