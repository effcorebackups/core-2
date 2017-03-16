<?php

namespace effectivecore\modules\user {
          abstract class table_role extends \effectivecore\modules\data\db_table {

  static $table_name = 'role';
  static $fields = [
    'id'       => ['type' => 'varchar(255)', 'not null', 'primary key' => true],
    'title'    => ['type' => 'varchar(255)', 'not null'],
    'is_embed' => ['type' => 'int(1)', 'default 0'],
  ];

  static function install() {
    parent::install();
    static::insert(['id' => 'anonymous',  'title' => 'Anonymous users', 'is_embed' => 1]);
    static::insert(['id' => 'logged',     'title' => 'Logged users',    'is_embed' => 1]);
    static::insert(['id' => 'admins',     'title' => 'Administrators']);
    static::insert(['id' => 'moderators', 'title' => 'Moderators']);
  }

}}