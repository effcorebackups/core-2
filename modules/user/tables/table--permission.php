<?php

namespace effectivecore\modules\user {
          abstract class table_permission extends \effectivecore\modules\storage\db_table {

  static $table_name = 'permission';
  static $fields = [
    'id'    => ['type' => 'varchar(255)', 'not null', 'primary key' => true],
    'title' => ['type' => 'varchar(255)', 'not null'],
  ];

  static function install() {
    parent::install();
    static::insert(['id' => 'user_profile_view',   'title' => 'User profile: view']);
    static::insert(['id' => 'user_profile_edit',   'title' => 'User profile: edit']);
    static::insert(['id' => 'user_profile_delete', 'title' => 'User profile: delete']);
  }

}}