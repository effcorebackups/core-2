<?php

namespace effectivecore {
          use \effectivecore\modules\storage\db_factory as db;
          use \effectivecore\modules\storage\storage_factory as storage;
          class storage_instance {

  public $id;       # means name for using in code
  public $name;     # means database name
  public $hostname;
  public $username;
  public $password;
  public $driver;
  public $is_init;

  function init() {
    if (empty($this->is_init)) {
      $this->is_init = db::init('mysql',
        storage::$data[$this->id]->hostname,
        storage::$data[$this->id]->name,
        storage::$data[$this->id]->username,
        storage::$data[$this->id]->password
      );
      if (!$this->is_init) {
        factory::send_header_and_exit('access_denided',
          'Database is unavailable!'
        );
      }
    }
  }

  function install_entity($entity) {
    $this->init();
    $field_sql = [];
    foreach ($entity->fields as $c_name => $c_info) {
      $c_prop = [$c_info->type.(isset($c_info->size) ? '('.$c_info->size.')' : '')];
      if (property_exists($c_info, 'unsigned')       && $c_info->unsigned)       $c_prop[] = 'unsigned';
      if (property_exists($c_info, 'auto_increment') && $c_info->auto_increment) $c_prop[] = 'auto_increment';
      if (property_exists($c_info, 'not_null')       && $c_info->not_null)       $c_prop[] = 'not null';
      if (property_exists($c_info, 'null')           && $c_info->null)           $c_prop[] = 'null';
      if (property_exists($c_info, 'default') && is_string($c_info->default))    $c_prop[] = 'default "'.$c_info->default.'"';
      if (property_exists($c_info, 'default') && is_int($c_info->default))       $c_prop[] = 'default "'.$c_info->default.'"';
      if (property_exists($c_info, 'default') && is_null($c_info->default))      $c_prop[] = 'default null';
      $field_sql[] = '`'.$c_name.'` '.implode(' ', $c_prop);
    }
    return db::query('CREATE TABLE `'.$entity->name.'` ('.(isset($entity->primary_keys) ?
      implode(', ', $field_sql).', PRIMARY KEY (`'.implode('`, `', $entity->primary_keys).'`)' :
      implode(', ', $field_sql)
    ).') default charset='.$entity->charset.';');
  }

  function uninstall_entity($entity) {
    $this->init();
    db::query('DROP TABLE `'.$entity->name.'`;');
  }

  function load_entity() {
  }

  function save_entity() {
  }

}}