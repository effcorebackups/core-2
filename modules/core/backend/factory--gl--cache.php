<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\console_factory as console;
          abstract class cache_factory extends \effectivecore\dynamic_factory {

  static $type = 'cache';
  static $directory = dir_dynamic.'cache/';

  static function update($name, $data, $info = null) {
    if (parent::update($name, $data, $info)) {
      console::add_log('storage', 'cache', 'Cache for "'.$name.'" was rebuilded.', 'ok', 0);
    }
  }

}}