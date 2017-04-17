<?php

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          abstract class table_user extends \effectivecore\modules\storage\db_table {

  static $table_name = 'user';
  static $fields = [
    'id'            => ['type' => 'int(11)', 'unsigned', 'not null', 'auto_increment', 'primary key' => true],
    'email'         => ['type' => 'varchar(255)', 'not null', 'default ""'],
    'password_hash' => ['type' => 'varchar(255)', 'not null', 'default ""'],
    'created'       => ['type' => 'timestamp', 'null', 'default null'],
    'is_locked'     => ['type' => 'int(1)', 'default 0'],
  ];

  static function install() {
    parent::install();
    static::insert(['id' => 1, 'email' => 'admin@example.com', 'password_hash' => sha1('12345'), 'created' => date(format_datetime, time()), 'is_locked' => 1]);
    static::insert(['id' => 2, 'email' => 'user@example.com',  'password_hash' => sha1('12345'), 'created' => date(format_datetime, time())]);
  }

}}