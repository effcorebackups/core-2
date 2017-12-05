<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\console_factory as console;
          abstract class temporary_factory extends \effectivecore\dynamic_factory {

  static $type = 'tmp';
  static $directory = dir_dynamic.'tmp/';

}}