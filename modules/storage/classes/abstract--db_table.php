<?php

namespace effectivecore\modules\storage {
          use \effectivecore\factory;
          abstract class db_table {

  static $table_name  = null;
  static $fields      = [];
  static $unique_keys = [];
  static $indexes     = [];
  static $charset     = 'utf8';

  static function install() {
    $fields_sql = [];
    $p_keys = [];
    foreach (static::$fields as $c_id => $c_info) {
      $fields_sql[] = $c_id.' '.implode(' ', ['primary key' => false] + $c_info);
      if (!empty($c_info['primary key'])) {
        $p_keys[] = $c_id;
      }
    }
    db::query('CREATE TABLE %T_'.static::$table_name.' ('.implode(', ', $fields_sql).(count($p_keys) ? ', primary key ('.implode(', ', $p_keys).')' : '').') default charset='.static::$charset);
  # add unique keys
    foreach (static::$unique_keys as $key_name => $fields) {
      db::query('ALTER TABLE %T_'.static::$table_name.' ADD UNIQUE KEY '.$key_name.' ('.implode(', ', $fields).')');
    }
  # add indexes
    foreach (static::$indexes as $index_name => $fields) {
      db::query('ALTER TABLE %T_'.static::$table_name.' ADD INDEX '.$index_name.' ('.implode(', ', $fields).')');
    }
  }

  static function uninstall() {
    db::query('DROP TABLE %T_'.static::$table_name);
  }

  static function select($fields = ['*'], $conditions = [], $order = [], $rcount = 0, $offset = 0) {
    if (static::$table_name) {
      return db::query('SELECT '.implode(', ', $fields).' '.
                       'FROM %T_'.static::$table_name.
                       (count($conditions) ? ' WHERE '.factory::data_to_attr($conditions, ' and ') : '').
                       (count($order) ? ' ORDER BY '.str_replace('!', ' DESC ', implode(', ', $order)) : '').
                       ($rcount ? ' LIMIT  '.$rcount : '').
                       ($offset ? ' OFFSET '.$offset : ''));
    }
  }

  static function select_one($fields = ['*'], $conditions = [], $order = []) {
    if (static::$table_name) {
      $res = db::query('SELECT '.implode(', ', $fields).' '.
                       'FROM %T_'.static::$table_name.
                       (count($conditions) ? ' WHERE '.factory::data_to_attr($conditions, ' and ') : '').
                       (count($order) ? ' ORDER BY '.str_replace('!', ' DESC ', implode(', ', $order)) : '').
                       (' LIMIT 1'));
      return reset($res);
    }
  }

  static function insert($row) {
    if (static::$table_name) {
      return db::query('INSERT INTO %T_'.static::$table_name.' ('.implode(', ', array_keys($row)).') '.
                       'VALUES ("'.implode('", "', array_values($row)).'")');
    }
  }

  static function update($fields, $conditions = []) {
    if (static::$table_name) {
      return db::query('UPDATE %T_'.static::$table_name.' '.
                       'SET '.factory::data_to_attr($fields, ', ').' '.
                       'WHERE '.factory::data_to_attr($conditions, ' and '));
    }
  }

  static function delete($conditions) {
    if (static::$table_name) {
      return db::query('DELETE FROM %T_'.static::$table_name.' '.
                       'WHERE '.factory::data_to_attr($conditions, ' and '));
    }
  }

}}