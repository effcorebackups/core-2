<?php

namespace effectivecore {

  const format_date     = 'Y-m-d';
  const format_time     = 'H:i:s';
  const format_datetime = 'Y-m-d H:i:s';
  const dir_root        = __DIR__.'/';
  const dir_cache       = __DIR__.'/cache/';
  const dir_modules     = __DIR__.'/modules/';
  const dir_settings    = __DIR__.'/settings/';
  const nl              = "\n";
  const br              = "<br/>";

  require_once('modules/core/classes/gl--factory--events.php');
  require_once('modules/core/classes/gl--factory--events_module.php');
  events_module_factory::on_init();

# init storage
  $storage = \effectivecore\modules\storage\storage_factory::get_instance('db_main');
# select instance
  $instance = new entity_instance('entities/user/user', ['id' => 1]);
  $storage->select_instance($instance);
  //print_R( $instance );
# insert instance
  $instance = new entity_instance('entities/user/user', [
    'email' => '',
    'password_hash' => sha1('12345'),
    'created' => date(format_datetime, time()),
    'is_locked' => 1,
  ]);
  $storage->insert_instance($instance);
  
}