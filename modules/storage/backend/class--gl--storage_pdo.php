<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\events_factory as events;
          use \effectivecore\message_factory as message;
          use \effectivecore\translations_factory as translations;
          class storage_pdo {

  public $id;
  public $driver;
  public $credentials;
  public $table_prefix = '';
  protected $connection;
  protected $queries = [];

  function init() {
    if ($this->connection) return
        $this->connection;
    else {
      if ($this->credentials) {
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
          return $this->connection;
        } catch (\PDOException $e) {
          message::add_new(
            translations::get('Storage %%_id is not available!', ['id' => $this->id]), 'warning'
          );
        }
      } else {
        message::add_new(
          translations::get('Credentials for storage %%_id was not setted!', ['id' => $this->id]), 'warning'
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

  function prepare_name($name) {
    switch ($this->driver) {
      case 'mysql' : return '`'.$this->table_prefix.$name.'`';
      case 'sqlite': return '"'.$this->table_prefix.$name.'"';
      case 'pgsql' : return '"'.$this->table_prefix.$name.'"';
    }
  }

  function prepare_field_name($name) {
    return $name;
  }

  function prepare_field_value($data) {
    return "'".$this->quote($data)."'";
  }

  function prepare_attributes($data, $mode = null, $delimiter = ', ') {
    $return = [];
    foreach ($data as $c_field => $c_value) {
      switch ($mode) {
        case 'order' : $return[] = $this->prepare_field_name($c_field).' '.$c_value; break;
        case 'fields': $return[] = $this->prepare_field_name($c_field); break;
        case 'values': $return[] = $this->prepare_field_value($c_value); break;
        default      : $return[] = $this->prepare_field_name($c_field).' = '.$this->prepare_field_value($c_value); break;
      }
    }
    return implode($delimiter, $return);
  }

  function quote($data) {
    return str_replace("'", "''", $data);
  }

  function transaction_begin()     {if ($this->init()) return $this->connection->beginTransaction();}
  function transaction_roll_back() {if ($this->init()) return $this->connection->rollBack();}
  function transaction_commit()    {if ($this->init()) return $this->connection->commit();}

  function query($query) {
    if ($this->init()) {
      $this->queries[] = $query;
      events::start('on_query_before', 'pdo', [&$this, &$query]);
      $result = $this->connection->query($query);
      $errors = $this->connection->errorInfo();
      events::start('on_query_after', 'pdo', [&$this, &$query, &$result, &$errors]);
      if ($errors[0] !== '00000') {
        message::add_new(
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
  }

  function get_queries() {
    return $this->queries;
  }

  function install_entity($entity) {
    if ($this->init()) {
      $fields = [];
      foreach ($entity->get_fields_info() as $c_name => $c_info) {
      # prepare field type
        switch ($c_info->type) {
          case 'autoincrement':
            if ($this->driver == 'mysql')  $c_properties = ['integer primary key auto_increment'];
            if ($this->driver == 'sqlite') $c_properties = ['integer primary key autoincrement'];
            if ($this->driver == 'pgsql')  $c_properties = ['serial primary key'];
            break;
          case 'blob':
            if ($this->driver == 'pgsql') {
              $c_info->type = 'bytea';
            }
          default:
            $c_properties = [$c_info->type.(isset($c_info->size) ?
                                              '('.$c_info->size.')' : '')];
        }
      # prepare field properties
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
    # prepare constraints
      $auto_name = $entity->get_auto_name();
      foreach ($entity->get_constraints_info() as $suffix => $c_info) {
        if ($c_info->fields != [$auto_name => $auto_name]) {
          $s_cstr_type = $c_info->type;
          $s_cstr_name = $this->prepare_name($entity->get_name().'_'.$suffix);
          $s_cstr_flds = implode(', ', $c_info->fields);
          $fields[] = 'CONSTRAINT '.$s_cstr_name.' '.$s_cstr_type.' ('.$s_cstr_flds.')';
        }
      }
    # create entity
      $s_table_name = $this->prepare_name($entity->get_name());
      $s_fields = implode(', ', $fields);
      $this->transaction_begin();
      $this->query('DROP TABLE IF EXISTS '.$s_table_name.';');
      $this->query('CREATE TABLE '.$s_table_name.' ('.$s_fields.');');
    # create indexes
      foreach ($entity->get_indexes_info() as $suffix => $c_info) {
        $s_idx_type = $c_info->type;
        $s_idx_name = $this->prepare_name($entity->get_name().'_'.$suffix);
        $s_idx_flds = implode(', ', $c_info->fields);
        $this->query('CREATE '.$s_idx_type.' '.$s_idx_name.' ON '.$s_table_name.' ('.$s_idx_flds.');');
      }
      return $this->transaction_commit();
    }
  }

  function uninstall_entity($entity) {
    if ($this->init()) {
      $s_table_name = $this->prepare_name($entity->get_name());
      $this->query('DROP TABLE '.$s_table_name.';');
    }
  }

  function select_instances($entity, $conditions = [], $order = [], $limit = 0, $offset = 0) {
    if ($this->init()) {
      $s_table_name = $this->prepare_name($entity->get_name());
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
  }

  function select_instance($instance) { # return: null | instance
    if ($this->init()) {
      $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys());
      $s_table_name = $this->prepare_name($instance->get_entity()->get_name());
      $s_where = $this->prepare_attributes($keys, null, ' and ');
      $result = $this->query('SELECT * FROM '.$s_table_name.' WHERE '.$s_where.' LIMIT 1;');
      if (isset($result[0])) {
        $instance->values = $result[0]->values;
        return $instance;
      }
    }
  }

  function insert_instance($instance) { # return: null | instance | instance + new_id
    if ($this->init()) {
      $auto_name = $instance->get_entity()->get_auto_name();
      $s_table_name = $this->prepare_name($instance->get_entity()->get_name());
      $s_fields = $this->prepare_attributes($instance->get_values(), 'fields');
      $s_values = $this->prepare_attributes($instance->get_values(), 'values');
      $new_id = $this->query('INSERT INTO '.$s_table_name.' ('.$s_fields.') VALUES ('.$s_values.');');
      if ($new_id !== null && $auto_name == null) return $instance;
      if ($new_id !== null && $auto_name != null) {
        $instance->values[$auto_name] = $new_id;
        return $instance;
      }
    }
  }

  function update_instance($instance) { # return: null | instance
    if ($this->init()) {
      $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys(true, false));
      $s_table_name = $this->prepare_name($instance->get_entity()->get_name());
      $s_changes = $this->prepare_attributes($instance->get_values());
      $s_where = $this->prepare_attributes($keys, null, ' and ');
      $row_count = $this->query('UPDATE '.$s_table_name.' SET '.$s_changes.' WHERE '.$s_where.';');
      if ($row_count === 1) {
        return $instance;
      }
    }
  }

  function delete_instance($instance) { # return: null | instance + empty(values)
    if ($this->init()) {
      $keys = array_intersect_key($instance->get_values(), $instance->get_entity()->get_keys(true, false));
      $s_table_name = $this->prepare_name($instance->get_entity()->get_name());
      $s_where = $this->prepare_attributes($keys, null, ' and ');
      $row_count = $this->query('DELETE FROM '.$s_table_name.' WHERE '.$s_where.';');
      if ($row_count === 1) {
        $instance->set_values([]);
        return $instance;
      }
    }
  }

}}