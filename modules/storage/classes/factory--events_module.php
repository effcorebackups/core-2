<?php

namespace effectivecore\modules\storage {
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class events_module_factory extends \effectivecore\events_module_factory {

  static function on_init() {
    storage::init();
  }

}}