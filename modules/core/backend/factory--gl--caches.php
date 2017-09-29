<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\console_factory as console;
          use \effectivecore\messages_factory as messages;
          abstract class caches_factory extends \effectivecore\dynamic_factory {

  static $type = 'cache';

}}