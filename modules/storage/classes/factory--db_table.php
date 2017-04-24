<?php

namespace effectivecore\modules\storage {
          use \effectivecore\factory;
          use \effectivecore\modules\storage\db_factory as db;
          abstract class db_table_factory {

  static $table_name  = null;
  static $fields      = [];
  static $unique_keys = [];
  static $indexes     = [];
  static $charset     = 'utf8';

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