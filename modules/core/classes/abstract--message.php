<?php

namespace effectivecore {
          use \effectivecore\modules\page\page;
          abstract class __message {

  static function set($message, $type = 'notice') {
    page::add_element(new message($message, $type), 'messages');
  }

}}