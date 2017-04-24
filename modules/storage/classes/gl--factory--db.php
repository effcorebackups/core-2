<?php

namespace effectivecore\modules\storage {
          use \effectivecore\timer_factory;
          use \effectivecore\console_factory as console;
          abstract class db_factory {

  static $connection;
  static $driver;
  static $host;
  static $database_name;
  static $username;
  static $password;
  static $table_prefix;
  static $queries = [];

  static function init($driver, $host, $database_name, $username, $password, $table_prefix = '') {
    try {
      static::$connection    = new \PDO("$driver:host=$host;dbname=$database_name", $username, $password);
      static::$driver        = $driver;
      static::$host          = $host;
      static::$database_name = $database_name;
      static::$username      = $username;
      static::$password      = $password;
      static::$table_prefix  = $table_prefix;
      return true;
    } catch (\PDOException $e) {
      return null;
    }
  }

  static function query($sql, $fetch_mode = null) {
    $sql = str_replace('%T_', static::$table_prefix, $sql);
    static::$queries[] = $sql;
    timer_factory::tap('sql_'.count(static::$queries));
    $query_result = static::$connection->query($sql);
    timer_factory::tap('sql_'.count(static::$queries));
    console::set_log(
      timer_factory::get_period('sql_'.count(static::$queries), 0, 1).' sec.', $sql, 'SQL queries'
    );
    switch (substr($sql, 0, 6)) {
      case 'SELECT':
        switch ($fetch_mode) {
          case 'first_cell':
            return $query_result->fetchColumn();
          default:
            $i = 0;
            $return = [];
            if ($query_result) {
              while ($row = $query_result->fetch(\PDO::FETCH_ASSOC)) {
                $return[isset($row['id']) ? $row['id'] : $i++] = $row;
              }
            }
            return $return;
        }
      case 'UPDATE': return $query_result->rowCount();
      case 'DELETE': return $query_result->rowCount();
      case 'INSERT': return static::$connection->lastInsertId();
    }
  }

  static function transaction_begin() {
    static::$connection->beginTransaction();
  }

  static function transaction_roll_back() {
    static::$connection->rollBack();
  }

  static function transaction_commit() {
    static::$connection->commit();
  }

}}