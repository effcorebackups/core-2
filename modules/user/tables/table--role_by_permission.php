<?php

namespace effectivecore\modules\user {
          abstract class table_role_by_permission extends \effectivecore\modules\storage\db_table {

  static $table_name = 'role_by_permission';
  static $fields = [
    'role_id'       => ['type' => 'varchar(255)', 'not null', 'primary key' => true],
    'permission_id' => ['type' => 'varchar(255)', 'not null', 'primary key' => true],
  ];

  static function install() {
    parent::install();
    static::insert(['role_id' => 'admins', 'permission_id' => 'user_profile_view']);
    static::insert(['role_id' => 'admins', 'permission_id' => 'user_profile_edit']);
    static::insert(['role_id' => 'admins', 'permission_id' => 'user_profile_delete']);
  }

}}