<?php

namespace effectivecore\modules\page {
          abstract class events extends \effectivecore\events {

  static function on_init() {
    page::init();
  }

}}