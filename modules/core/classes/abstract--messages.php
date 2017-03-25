<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class messages {

  static function set($message, $type = 'notice') {
    page::add_element(new message($message, $type), 'messages');
  }

}}