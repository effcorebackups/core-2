<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class message {

  static function set($message, $type = 'notice') {
    page::add_element(new message_n($message, $type), 'messages');
  }

}}