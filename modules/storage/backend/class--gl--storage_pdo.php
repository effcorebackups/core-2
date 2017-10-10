<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\timers_factory as timers;
          use \effectivecore\events_factory as events;
          use \effectivecore\console_factory as console;
          use \effectivecore\messages_factory as messages;
          class storage_pdo {

  public $id;
  public $connection;
  public $database_name;
  public $host_name;
  public $user_name;
  public $password;
  public $driver;
  public $is_init = false;
  public $queries = [];

  function init() {
    if (empty($this->is_init)) {
      try {
        events::start('on_storage_init_before', 'pdo', [&$this]);
        $this->connection = new \PDO($this->driver.':host='.
                                     $this->host_name.';dbname='.
                                     $this->database_name,
                                     $this->user_name,
                                     $this->password);
        events::start('on_storage_init_after', 'pdo', [&$this]);
        $this->is_init = true;
      } catch (\PDOException $e) {
        factory::send_header_and_exit('access_denided',
          'The PDO database "'.$this->id.'" is unavailable!'
        );
      }
    }
  }

  function test($data = []) {
    try {
      $connection = new \PDO(
        $data['driver'].':host='.
        $data['host_name'].';dbname='.
        $data['database_name'],
        $data['user_name'],
        $data['password']);
      $connection = null;
      return true;
    } catch (\PDOException $e) {
      return false;
    }
  }

  function transaction_begin()     {$this->init(); $this->connection->beginTransaction();}
  function transaction_roll_back() {$this->init(); $this->connection->rollBack();}
  function transaction_commit()    {$this->init(); $this->connection->commit();}

  function query($query) {
    $this->init();
    $this->queries[] = $query;
    events::start('on_query_before', 'pdo', [&$this, &$query]);
    $result = $this->connection->query($query);
    $errors = $this->connection->errorInfo();
    events::start('on_query_after', 'pdo', [&$this, &$query, &$result, &$errors]);
    if ($errors[0] != '00000') {
      messages::add_new(
        'Query error! '.br.
        'SQLSTATE: '.$errors[0].br.
        'Driver error code: '.$errors[1].br.
        'Driver error text: '.$errors[2], 'error'
      );
      return null;
    }
    switch (substr($query, 0, 6)) {
      case 'SELECT': return $result ? $result->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\effectivecore\instance') : null;
      case 'INSERT': return $this->connection->lastInsertId();
      case 'UPDATE': return $result->rowCount();
      case 'DELETE': return $result->rowCount();
    }
  }

  function install_entity($entity) {
    $this->init();
    $field_desc = [];
    foreach ($entity->get_fields_info() as $c_name => $c_info) {
      $c_properties = [$c_info->type.(isset($c_info->size) ? '('.$c_info->size.')' : '')];
      if (property_exists($c_info, 'unsigned')       && $c_info->unsigned)       $c_properties[] = 'unsigned';
      if (property_exists($c_info, 'auto_increment') && $c_info->auto_increment) $c_properties[] = 'auto_increment';
      if (property_exists($c_info, 'not_null')       && $c_info->not_null)       $c_properties[] = 'not null';
      if (property_exists($c_info, 'null')           && $c_info->null)           $c_properties[] = 'null';
      if (property_exists($c_info, 'default')) {
        if     ($c_info->default === 0)                   $c_properties[] = 'default 0';
        elseif ($c_info->default === null)                $c_properties[] = 'default null';
        elseif ($c_info->default === 'current_timestamp') $c_properties[] = 'default current_timestamp';
        else                                              $c_properties[] = 'default "'.$c_info->default.'"';
      }
      $field_desc[] = '`'.$c_name.'` '.implode(' ', $c_properties);
    }
    foreach ($entity->get_indexes_info() as $c_info) {
      $field_desc[] = $c_info->type.' (`'.implode('`, `', $c_info->fields).'`)';
    }
    $this->query(
      'CREATE TABLE `'.$entity->get_name().'` ('.implode(', ', $field_desc).') '.
      'default charset='.$entity->charset.';'
    );
  }

  function uninstall_entity($entity) {
    $this->init();
    $this->query('DROP TABLE `'.$entity->get_name().'`;');
  }

  function select_instance_set($entity, $conditions = [], $order = [], $count = 0, $offset = 0) {
    $this->init();
    $result = $this->query(
      'SELECT `'.implode('`, `', $entity->get_fields()).'` '.
      'FROM `'.$entity->get_name().'`'.
      (count($conditions) ? ' WHERE '.factory::data_to_attr($conditions, ' and ', '`') : '').
      (count($order)      ? ' ORDER BY `'.str_replace('!', ' DESC ', implode('`, `', $order)).'`' : '').
      ($count             ? ' LIMIT ' .$count  : '').
      ($offset            ? ' OFFSET '.$offset : '').';'
    );
    foreach ($result as $c_instance) {
      $c_instance->set_entity_name($entity->name);
    }
    return $result;
  }

  function select_instance($instance) { # return: null | instance
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity_keys());
    $p_table_name = '`'.$instance->get_entity_name().'`';
    $p_where = factory::data_to_attr($keys, ' and ', '`');
    $result = $this->query('SELECT * FROM '.$p_table_name.' WHERE '.$p_where.' LIMIT 1;');
    if (isset($result[0])) {
      $instance->values = $result[0]->values;
      return $instance;
    }
  }

  function insert_instance($instance) { # return: null | instance | instance + new_id
    $this->init();
    $auto_increment = $instance->get_entity_auto_increment();
    $p_table_name = '`'.$instance->get_entity_name().'`';
    $p_fields = '`'.implode('`, `', array_keys($instance->get_values())).'`';
    $p_values = '"'.implode('", "', $instance->get_values()).'"';
    $new_id = $this->query('INSERT INTO '.$p_table_name.' ('.$p_fields.') VALUES ('.$p_values.');');
    if ($new_id !== null && $auto_increment == null) return $instance;
    if ($new_id !== null && $auto_increment != null) {
      $instance->values[$auto_increment] = $new_id;
      return $instance;
    }
  }

  function update_instance($instance) { # return: null | instance
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity_keys());
    $p_table_name = '`'.$instance->get_entity_name().'`';
    $p_changes = factory::data_to_attr($instance->get_values(), ', ', '`');
    $p_where = factory::data_to_attr($keys, ' and ', '`');
    $row_count = $this->query('UPDATE '.$p_table_name.' SET '.$p_changes.' WHERE '.$p_where.' LIMIT 1;');
    if ($row_count === 1) {
      return $instance;
    }
  }

  function delete_instance($instance) { # return: null | instance + empty(values)
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity_keys());
    $p_table_name = '`'.$instance->get_entity_name().'`';
    $p_where = factory::data_to_attr($keys, ' and ', '`');
    $row_count = $this->query('DELETE FROM '.$p_table_name.' WHERE '.$p_where.' LIMIT 1;');
    if ($row_count === 1) {
      $instance->set_values([]);
      return $instance;
    }
  }

}}