<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\event_factory as event;
          use \effectivecore\message_factory as message;
          use \effectivecore\translation_factory as translation;
          class storage_pdo {

  public $id;
  public $driver;
  public $credentials;
  public $table_prefix = '';
  protected $queries = [];
  protected $connection;

  function init() {
    if ($this->connection) return
        $this->connection;
    else {
      if ($this->credentials) {
        try {
          event::start('on_storage_init_before', 'pdo', [&$this]);
          switch ($this->driver) {
            case 'mysql':
              $this->connection = new \PDO(
                $this->driver.':host='.
                $this->credentials->host_name.';port='.
                $this->credentials->port.';dbname='.
                $this->credentials->storage_name,
                $this->credentials->user_name,
                $this->credentials->password);
              break;
            case 'sqlite':
              $this->connection = new \PDO(
                $this->driver.':'.dir_dynamic.'data/'.
                $this->credentials->file_name);
              break;
          }
          event::start('on_storage_init_after', 'pdo', [&$this]);
          return $this->connection;
        } catch (\PDOException $e) {
          message::add_new(
            translation::get('Storage %%_id is not available!', ['id' => $this->id]), 'warning'
          );
        }
      } else {
        message::add_new(
          translation::get('Credentials for storage %%_id was not setted!', ['id' => $this->id]), 'warning'
        );
      }
    }
  }

  function test($driver, $params = []) {
    try {
      switch ($driver) {
        case 'mysql':
          $connection = new \PDO(
            $driver.':host='.
            $params->host_name.';port='.
            $params->port.';dbname='.
            $params->storage_name,
            $params->user_name,
            $params->password);
          break;
        case 'sqlite':
          $file_path = dir_dynamic.'data/'.$params->file_name;
          $connection = new \PDO($driver.':'.$file_path);
          if (!is_writable($file_path)) {
            throw new \Exception('File is not writable!');
          }
          break;
      }
      $connection = null;
      return true;
    } catch (\PDOException $e) {
      return ['message' => $e->getMessage(), 'code' => $e->getCode()];
    } catch (\Exception $e) {
      return ['message' => $e->getMessage(), 'code' => $e->getCode()];
    }
  }

  function get_id()           {return $this->id;}
  function get_driver()       {return $this->driver;}
  function get_table_prefix() {return $this->table_prefix;}
  function get_queries()      {return $this->queries;}

  function transaction_begin()     {if ($this->init()) return $this->connection->beginTransaction();}
  function transaction_roll_back() {if ($this->init()) return $this->connection->rollBack();}
  function transaction_commit()    {if ($this->init()) return $this->connection->commit();}

  function prepare_name($name) {
    switch ($this->driver) {
      case 'mysql' : return '`'.$this->table_prefix.$name.'`';
      case 'sqlite': return '"'.$this->table_prefix.$name.'"';
    }
  }

  function prepare_field_name($name) {
    return $name;
  }

  function prepare_field_value($data, $type) {
    if ($type == 'blob') return "X'".bin2hex($this->value_quote($data))."'";
    return "'".$this->value_quote($data)."'";
  }

  function prepare_attributes($data, $entity, $mode = null, $delimiter = ', ') {
    $return = [];
    foreach ($data as $c_name => $c_value) {
      $c_type = $entity->get_field_info($c_name)->type;
      switch ($mode) {
        case 'order' : $return[] = $this->prepare_field_name($c_name).' '.$c_value; break;
        case 'fields': $return[] = $this->prepare_field_name($c_name); break;
        case 'values': $return[] = $this->prepare_field_value($c_value, $c_type); break;
        default      : $return[] = $this->prepare_field_name($c_name).' = '.$this->prepare_field_value($c_value, $c_type); break;
      }
    }
    return implode($delimiter, $return);
  }

  function query(...$query) {
    if (is_array($query[0])) $query = $query[0];
    if ($this->init()) {
      $this->queries[] = $query;
    # event::start('on_query_before', 'pdo', [&$this, &$query]);
      $result = $this->connection->query(implode(' ', $query).';');
      $errors = $this->connection->errorInfo();
    # event::start('on_query_after', 'pdo', [&$this, &$query, &$result, &$errors]);
      if ($errors[0] !== '00000') {
        message::add_new(
          'Query error! '.br.
          'SQLState: '.$errors[0].br.
          'Driver error code: '.$errors[1].br.
          'Driver error text: '.$errors[2], 'error'
        );
        return null;
      }
      switch ($query[0]) {
        case 'SELECT': return $result ? $result->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\effectivecore\instance') : null;
        case 'INSERT': return $this->connection->lastInsertId();
        case 'UPDATE': return $result->rowCount();
        case 'DELETE': return $result->rowCount();
        default      : return $result;
      }
    }
  }

  function value_quote($value) {
    return str_replace("'", "''", $value);
  }

  function tables(...$tables) {
    $return = [];
    foreach ($tables as $c_table) {
      switch ($this->driver) {
        case 'mysql' : $return[] = '`'.$this->table_prefix.$c_table.'`'; break;
        case 'sqlite': $return[] = '"'.$this->table_prefix.$c_table.'"'; break;
      }
    }
    return implode(', ', $return);
  }

  function fields(...$fields) {
    return implode(', ', is_array($fields[0]) ?
                                  $fields[0] : $fields);
  }

  function values(...$values) {
    $return = [];
    foreach (is_array($values[0]) ?
                      $values[0] : $values as $c_value) {
      $return[] = "'".$this->value_quote($c_value)."'";
    }
    return implode(', ', $return);
  }

  function condition($field, $value, $op = '=') {
    return $this->fields($field).' '.$op.' '.
           $this->values($value);
  }

  function op($op) {
    return $op;
  }

  function _where($data, $op = 'and') {
    $return = [];
    foreach ($data as $field => $value) {
      $return[] = $this->condition($field, $value);
    }
    return implode(' '.$this->op($op).' ', $return);
  }





  function query_old($query) {
    if ($this->init()) {
      $this->queries[] = $query;
      event::start('on_query_before', 'pdo', [&$this, &$query]);
      $result = $this->connection->query($query);
      $errors = $this->connection->errorInfo();
      event::start('on_query_after', 'pdo', [&$this, &$query, &$result, &$errors]);
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

  ################
  ### entities ###
  ################

  function install_entity($entity) {
    if ($this->init()) {
      $fields = [];
      foreach ($entity->get_fields_info() as $c_name => $c_info) {
      # prepare field type
        switch ($c_info->type) {
          case 'autoincrement':
            if ($this->driver == 'mysql')  $c_properties = ['integer primary key auto_increment'];
            if ($this->driver == 'sqlite') $c_properties = ['integer primary key autoincrement'];
            break;
          default:
            $c_properties = [$c_info->type.(isset($c_info->size) ?
                                              '('.$c_info->size.')' : '')];
        }
      # prepare field properties
        if (property_exists($c_info, 'not_null') && $c_info->not_null) $c_properties[] = 'not null';
        if (property_exists($c_info, 'null')     && $c_info->null)     $c_properties[] = 'null';
        if (property_exists($c_info, 'default')) {
          if     ($c_info->default === 0)    $c_properties[] = 'default 0';
          elseif ($c_info->default === null) $c_properties[] = 'default null';
          else                               $c_properties[] = 'default \''.$c_info->default.'\'';
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
      $this->query_old('DROP TABLE IF EXISTS '.$s_table_name.';');
      $this->query_old('CREATE TABLE '.$s_table_name.' ('.$s_fields.');');
    # create indexes
      foreach ($entity->get_indexes_info() as $suffix => $c_info) {
        $s_idx_type = $c_info->type;
        $s_idx_name = $this->prepare_name($entity->get_name().'_'.$suffix);
        $s_idx_flds = implode(', ', $c_info->fields);
        $this->query_old('CREATE '.$s_idx_type.' '.$s_idx_name.' ON '.$s_table_name.' ('.$s_idx_flds.');');
      }
      return $this->transaction_commit();
    }
  }

  function uninstall_entity($entity) {
    if ($this->init()) {
      $s_table_name = $this->prepare_name($entity->get_name());
      $this->query_old('DROP TABLE '.$s_table_name.';');
    }
  }

  function select_instances($entity, $conditions = [], $order = [], $limit = 0, $offset = 0) {
    if ($this->init()) {
      $s_table_name = $this->prepare_name($entity->get_name());
      $s_conditions = count($conditions) ? ' WHERE '.$this->prepare_attributes($conditions, $entity, null, ' and ') : '';
      $s_order = count($order) ? ' ORDER BY '.$this->prepare_attributes($order, $entity, 'order') : '';
      $s_limit = $limit ? ' LIMIT ' .$limit : '';
      $s_offset = $offset ? ' OFFSET '.$offset : '';
      $result = $this->query_old('SELECT * FROM '.$s_table_name.$s_conditions.$s_order.$s_limit.$s_offset.';');
      foreach ($result as $c_instance) {
        $c_instance->set_entity_name($entity->get_name());
      }
      return $result;
    } 
  }

  function select_instance($instance) { # return: null | instance
    if ($this->init()) {
      $entity = $instance->get_entity();
      $keys = array_intersect_key($instance->get_values(), $entity->get_keys());
      $result = $this->query(
        'SELECT', '*',
        'FROM', $this->tables($entity->get_name()),
        'WHERE', $this->_where($keys),
        'LIMIT', 1);
      if (isset($result[0])) {
        foreach ($result[0]->values as $c_name => $c_value) {
          $instance->{$c_name} = $c_value;
        }
        return $instance;
      }
    }
  }

  function insert_instance($instance) { # return: null | instance | instance + new_id
    if ($this->init()) {
      $entity = $instance->get_entity();
      $values = array_intersect_key($instance->get_values(), $entity->get_fields());
      $fields = array_keys($values);
      $auto_name = $entity->get_auto_name();
      $new_id = $this->query(
        'INSERT', 'INTO', $this->tables($entity->get_name()), '(',
                          $this->fields($fields), ')',
        'VALUES', '(',    $this->values($values), ')');
      if ($new_id !== null && $auto_name == null) return $instance;
      if ($new_id !== null && $auto_name != null) {
        $instance->{$auto_name} = $new_id;
        return $instance;
      }
    }
  }

  function update_instance($instance) { # return: null | instance
    if ($this->init()) {
      $entity = $instance->get_entity();
      $keys = array_intersect_key($instance->get_values(), $entity->get_keys(true, false));
      $values = array_intersect_key($instance->get_values(), $entity->get_fields());
      $s_table_name = $this->prepare_name($entity->get_name());
      $s_changes = $this->prepare_attributes($values, $entity);
      $s_where = $this->prepare_attributes($keys, $entity, null, ' and ');
      $row_count = $this->query_old('UPDATE '.$s_table_name.' SET '.$s_changes.' WHERE '.$s_where.';');
      if ($row_count === 1) {
        return $instance;
      }
    }
  }

  function delete_instance($instance) { # return: null | instance + empty(values)
    if ($this->init()) {
      $entity = $instance->get_entity();
      $keys = array_intersect_key($instance->get_values(), $entity->get_keys(true, false));
      $s_table_name = $this->prepare_name($entity->get_name());
      $s_where = $this->prepare_attributes($keys, $entity, null, ' and ');
      $row_count = $this->query_old('DELETE FROM '.$s_table_name.' WHERE '.$s_where.';');
      if ($row_count === 1) {
        $instance->set_values([]);
        return $instance;
      }
    }
  }

}}