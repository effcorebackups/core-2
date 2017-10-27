<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\events_factory as events;
          use \effectivecore\messages_factory as messages;
          class storage_pdo {

  public $id;
  public $is_init = false;
  public $connection;
  public $driver;
  public $credentials;
  public $table_prefix = '';
  public $queries = [];

  function init() {
    if (empty($this->is_init)) {
      try {
        events::start('on_storage_init_before', 'pdo', [&$this]);
        switch ($this->driver) {
          case 'sqlite':
            $this->connection = new \PDO(
              $this->driver.':'.dir_dynamic.'data/'.
              $this->credentials->file_name);
            break;
          default:
            $this->connection = new \PDO(
              $this->driver.':host='.
              $this->credentials->host_name.';dbname='.
              $this->credentials->storage_name,
              $this->credentials->user_name,
              $this->credentials->password);
            break;       
        }
        events::start('on_storage_init_after', 'pdo', [&$this]);
        $this->is_init = true;
      } catch (\PDOException $e) {
        factory::send_header_and_exit('access_denided',
          'Storage '.$this->id.' is not available!'
        );
      }
    }
  }

  function test($driver, $params = []) {
    try {
      switch ($driver) {
        case 'sqlite':
          $connection = new \PDO(
            $driver.':'.dir_dynamic.'data/'.
            $params->file_name);
          break;
        default:
          $connection = new \PDO(
            $driver.':host='.
            $params->host_name.';dbname='.
            $params->storage_name,
            $params->user_name,
            $params->password);
          break;       
      }
      $connection = null;
      return true;
    } catch (\PDOException $e) {
      return ['message' => $e->getMessage(), 'code' => $e->getCode()];
    }
  }

  function prepare_table_name($name) {
    switch ($this->driver) {
      case 'mysql' : return '`'.$this->table_prefix.$name.'`';
      case 'sqlite': return '"'.$this->table_prefix.$name.'"';
      case 'pgsql' : return '"'.$this->table_prefix.$name.'"';
    }
  }

  function prepare_field($name) {
    return $name;
  }

  function prepare_value($data) {
    return "'".$this->quote($data)."'";
  }

  function prepare_attributes($data, $mode = null, $delimiter = ', ') {
    $return = [];
    foreach ($data as $c_field => $c_value) {
      switch ($mode) {
        case 'order' : $return[] = $this->prepare_field($c_field).' '.$c_value; break;
        case 'fields': $return[] = $this->prepare_field($c_field); break;
        case 'values': $return[] = $this->prepare_value($c_value); break;
        default      : $return[] = $this->prepare_field($c_field).' = '.$this->prepare_value($c_value); break;
      }
    }
    return implode($delimiter, $return);
  }

  function quote($data) {
    return str_replace("'", "''", $data);
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
    if ($errors[0] !== '00000') {
      messages::add_new(
        'Query error! '.br.
        'SQLState: '.$errors[0].br.
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
      default      : return $result;
    }
  }

  function install_entity($entity) {
    $this->init();
    $fields = [];
    foreach ($entity->get_fields_info() as $c_name => $c_info) {
    # define field type
      switch ($c_info->type) {
        case 'serial':
          if ($this->driver == 'mysql')  $c_properties = ['integer auto_increment'];
          if ($this->driver == 'sqlite') $c_properties = ['integer autoincrement'];
          if ($this->driver == 'pgsql')  $c_properties = ['serial'];
          break;
        default: $c_properties = [$c_info->type.(isset($c_info->size) ?
                                                   '('.$c_info->size.')' : '')];
      }
    # define field properties
      if (property_exists($c_info, 'not_null') && $c_info->not_null) $c_properties[] = 'not null';
      if (property_exists($c_info, 'null')     && $c_info->null)     $c_properties[] = 'null';
      if (property_exists($c_info, 'default')) {
        if     ($c_info->default === 0)                   $c_properties[] = 'default 0';
        elseif ($c_info->default === null)                $c_properties[] = 'default null';
        elseif ($c_info->default === 'current_timestamp') $c_properties[] = 'default current_timestamp';
        else                                              $c_properties[] = 'default "'.$c_info->default.'"';
      }
      $fields[] = $c_name.' '.implode(' ', $c_properties);
    }
  # define indexes
    foreach ($entity->get_indexes_info() as $c_info) {
      $fields[] = $c_info->type.' ('.implode(', ', $c_info->fields).')';
    }
  # create table
    $s_table_name = $this->prepare_table_name($entity->get_name());
    $this->query('DROP TABLE IF EXISTS '.$s_table_name.';');
    return $this->query( 'CREATE TABLE '.$s_table_name.' ('.implode(', ', $fields).');');
  }

  function uninstall_entity($entity) {
    $this->init();
    $s_table_name = $this->prepare_table_name($entity->get_name());
    $this->query('DROP TABLE '.$s_table_name.';');
  }

  function select_instances($entity, $conditions = [], $order = [], $limit = 0, $offset = 0) {
    $this->init();
    $s_table_name = $this->prepare_table_name($entity->get_name());
    $s_conditions = count($conditions) ? ' WHERE '.$this->prepare_attributes($conditions, null, ' and ') : '';
    $s_order = count($order) ? ' ORDER BY '.$this->prepare_attributes($order, 'order') : '';
    $s_limit = $limit ? ' LIMIT ' .$limit : '';
    $s_offset = $offset ? ' OFFSET '.$offset : '';
    $result = $this->query('SELECT * FROM '.$s_table_name.$s_conditions.$s_order.$s_limit.$s_offset.';');
    foreach ($result as $c_instance) {
      $c_instance->set_entity_name($entity->name);
    }
    return $result;
  }

  function select_instance($instance) { # return: null | instance
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys());
    $s_table_name = $this->prepare_table_name($instance->get_entity()->get_name());
    $s_where = $this->prepare_attributes($keys, null, ' and ');
    $result = $this->query('SELECT * FROM '.$s_table_name.' WHERE '.$s_where.' LIMIT 1;');
    if (isset($result[0])) {
      $instance->values = $result[0]->values;
      return $instance;
    }
  }

  function insert_instance($instance) { # return: null | instance | instance + new_id
    $this->init();
    $serial_id = $instance->get_entity()->get_serial_id();
    $s_table_name = $this->prepare_table_name($instance->get_entity()->get_name());
    $s_fields = $this->prepare_attributes($instance->get_values(), 'fields');
    $s_values = $this->prepare_attributes($instance->get_values(), 'values');
    $new_id = $this->query('INSERT INTO '.$s_table_name.' ('.$s_fields.') VALUES ('.$s_values.');');
    if ($new_id !== null && $serial_id == null) return $instance;
    if ($new_id !== null && $serial_id != null) {
      $instance->values[$serial_id] = $new_id;
      return $instance;
    }
  }

  function update_instance($instance) { # return: null | instance
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys(['primary key']));
    $s_table_name = $this->prepare_table_name($instance->get_entity()->get_name());
    $s_changes = $this->prepare_attributes($instance->get_values());
    $s_where = $this->prepare_attributes($keys, null, ' and ');
    $row_count = $this->query('UPDATE '.$s_table_name.' SET '.$s_changes.' WHERE '.$s_where.';');
    if ($row_count === 1) {
      return $instance;
    }
  }

  function delete_instance($instance) { # return: null | instance + empty(values)
    $this->init();
    $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys(['primary key']));
    $s_table_name = $this->prepare_table_name($instance->get_entity()->get_name());
    $s_where = $this->prepare_attributes($keys, null, ' and ');
    $row_count = $this->query('DELETE FROM '.$s_table_name.' WHERE '.$s_where.';');
    if ($row_count === 1) {
      $instance->set_values([]);
      return $instance;
    }
  }

}}