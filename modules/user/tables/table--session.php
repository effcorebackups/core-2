<?php

namespace effectivecore\modules\user {
          abstract class table_session extends \effectivecore\modules\storage\db_table_factory {

  static $table_name = 'session';
  static $fields = [
    'id'       => ['type' => 'varchar(255)', 'not null', 'primary key' => true],
    'user_id'  => ['type' => 'int(11)', 'unsigned', 'not null'],
    'created'  => ['type' => 'timestamp', 'null', 'default null'],
    'data'     => ['type' => 'longblob', 'null', 'default null'],
  ];

}}