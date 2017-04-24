<?php

namespace effectivecore\modules\storage {
          use \effectivecore\settings_factory;
          use \effectivecore\factory;
          abstract class events_module extends \effectivecore\events_module {

  static function on_init() {
    storage_factory::init();
  # old code
    $is_init = db_factory::init(
      settings_factory::$data['db']['storage']->prod->driver,
      settings_factory::$data['db']['storage']->prod->host,
      settings_factory::$data['db']['storage']->prod->database_name,
      settings_factory::$data['db']['storage']->prod->username,
      settings_factory::$data['db']['storage']->prod->password,
      settings_factory::$data['db']['storage']->prod->table_prefix
    );
    if (!$is_init) {
      factory::send_header_and_exit('access_denided',
        'Database is unavailable!'
      );
    }
  }

}}