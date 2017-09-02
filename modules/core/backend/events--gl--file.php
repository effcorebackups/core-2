<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          abstract class events_file {

  static function on_file_load_before($file) {
    $relative = $file->get_path_relative();
    timers::tap('load_'.$relative);
  }

  static function on_file_load_after($file) {
    $relative = $file->get_path_relative();
    timers::tap('load_'.$relative);
    console::add_log(
      'load', $relative, 'ok', timers::get_period('load_'.$relative, -1, -2)
    );
  }

}}