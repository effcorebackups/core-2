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

  $storage = \effectivecore\modules\storage\storage_factory::get_instance('db_main');
  if ($storage) {
  # create instance dummy
    $instance = new entity_instance('entities/user/user', [
      'email'         => 'test@example.com',
      'password_hash' => sha1('12345'),
      'created'       => date(format_datetime, time()),
      'is_locked'     => 0,
    ]);
  # insert instance
    if ($storage->insert_instance($instance)) {
      print 'new instance "user" was inserted with ID = '.$instance->get_values()['id'].'!'.br;
    # update instance
      $instance->set_value('is_locked', '1');
      if ($storage->update_instance($instance)) {
        print 'instance was updated!'.br;
      # select instance
        $storage->select_instance($instance);
        print 'instance was selected:'.br;
        foreach ($instance->get_values() as $key => $value) {
          print '# '.$key.' = '.$value.br;
        }
      # delete instance
        $storage->delete_instance($instance);
      }
    };
  }

}