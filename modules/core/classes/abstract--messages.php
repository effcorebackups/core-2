<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class messages {

  static function add_new($message, $type = 'notice') {
    page::add_element(new message($message, $type), 'messages');
  }

}}