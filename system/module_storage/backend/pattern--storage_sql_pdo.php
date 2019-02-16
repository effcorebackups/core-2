<?php

  ##################################################################
  ### Copyright © 2017—2019 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          use \PDO as pdo;
          use \PDOException as pdo_exception;
          class storage_sql_pdo implements has_external_cache {

  public $name;
  public $driver;
  public $credentials = [];
  public $table_prefix = '';
  public $args = [];
  public $errors = [];
  protected $queries = [];
  protected $connection;

  function init($driver = null, $credentials = [], $table_prefix = '') {
    if ($this->connection) return
        $this->connection;
    else {
      if ($driver)       $this->driver       = $driver;
      if ($credentials)  $this->credentials  = $credentials;
      if ($table_prefix) $this->table_prefix = $table_prefix;
      if ($this->driver &&
          $this->credentials) {
        try {
          event::start('on_storage_init_before', 'pdo', [&$this]);
          switch ($this->driver) {
            case 'mysql':
              $this->connection = new pdo(
                $this->driver.           ':host='.
                $this->credentials->host.';port='.
                $this->credentials->port.';dbname='.
                $this->credentials->database,
                $this->credentials->login,
                $this->credentials->password);
              break;
            case 'sqlite':
              $this->connection = new pdo(
                $this->driver.':'.data::directory.
                $this->credentials->file_name);
              $this->query(['action' => 'PRAGMA', 'command' => 'encoding',     '=' => '=', 'value' => '"UTF-8"']);
              $this->query(['action' => 'PRAGMA', 'command' => 'foreign_keys', '=' => '=', 'value' => 'ON'     ]);
              break;
          }
          event::start('on_storage_init_after', 'pdo', [&$this]);
          return $this->connection;
        } catch (pdo_exception $e) {
          message::insert(
            translation::get('Storage %%_name is not available!', ['name' => $this->name]), 'error'
          );
        }
      } else {
        $path = (new file(data::directory.'changes.php'))->path_relative_get();
        $link = (new markup('a', ['href' => '/install/en'], 'Installation'))->render();
        message::insert(
          translation::get('Credentials for storage %%_name was not set!', ['name' => $this->name]).br.
          translation::get('Restore the storage credentials in "%%_file" or reinstall this system on the page: %%_link', ['file' => $path, 'link' => $link]), 'error'
        );
      }
    }
  }

  function test($driver, $credentials = []) {
    try {
      switch ($driver) {
        case 'mysql':
          $connection = new pdo(
            $driver.           ':host='.
            $credentials->host.';port='.
            $credentials->port.';dbname='.
            $credentials->database,
            $credentials->login,
            $credentials->password);
          break;
        case 'sqlite':
          $path = data::directory.$credentials->file_name;
          $connection = new pdo($driver.':'.$path);
          if (!is_writable($path)) {
            throw new \Exception('File is not writable!');
          }
          break;
      }
      $connection = null;
      return true;
    } catch (pdo_exception $e) {
      return ['message' => $e->getMessage(), 'code' => $e->getCode()]; } catch (\Exception $e) {
      return ['message' => $e->getMessage(), 'code' => $e->getCode()];
    }
  }

  function title_get() {
    if ($this->init()) {
      if ($this->driver == 'mysql' ) return 'MySQL' ;
      if ($this->driver == 'sqlite') return 'SQLite';
    }
  }

  function version_get() {
    if ($this->init()) {
      if ($this->driver == 'mysql' ) return $this->query(['action' => 'SELECT', 'command' => 'version()',        'alias_begin' => 'as', 'alias' => 'version'])[0]->version;
      if ($this->driver == 'sqlite') return $this->query(['action' => 'SELECT', 'command' => 'sqlite_version()', 'alias_begin' => 'as', 'alias' => 'version'])[0]->version;
    }
  }

  function transaction_begin()     {if ($this->init()) return $this->connection->beginTransaction();}
  function transaction_roll_back() {if ($this->init()) return $this->connection->rollBack();        }
  function transaction_commit()    {if ($this->init()) return $this->connection->commit();          }

  function query_prepare(&$query = [], $is_emulation = false) {
    foreach ($query as $c_key => &$c_value) {
      $c_modifier = strrchr($c_key, '!');
      if (is_array($c_value)) {
        $this->query_prepare($c_value, $is_emulation);
        switch ($c_modifier) {
          case '!,':
          case '!=':
            $c_new_values = [];
            foreach ($c_value as $c_sub_key => $c_sub_values) {
              if (!is_int($c_sub_key))
                   $c_new_values[$c_sub_key] = $c_sub_values;
              else $c_new_values[]           = $c_sub_values;
              $c_new_values[] = ltrim($c_modifier, '!');
            }
            array_pop($c_new_values);
            $c_value = $c_new_values;
            break;
        }
      } else {
        switch ($c_modifier) {
          case '!t': $c_value = $this->table($c_value);                break;
          case '!f': $c_value = $this->field($c_value);                break;
          case '!v': $c_value = $this->value($c_value, $is_emulation); break;
        }
      }
    }
  }

  function query(...$query) {
    if (is_array($query[0])) $query = $query[0];
    if ($this->init()) {
      event::start('on_query_before', 'pdo', [&$this, &$query]);
      $this->queries[] = $query_prepared = $query;
      $this->query_prepare($query_prepared);
      $query_flat = core::array_values_select_recursive($query_prepared);
      $query_flat_string = implode(' ', $query_flat).';';
      $result = $this->connection->prepare($query_flat_string);
      if ($result) $result->execute($this->args);
      $c_error = $result ? $result->errorInfo() : ['query preparation return the false', 'no', 'no'];
      event::start('on_query_after', 'pdo', [&$this, $query, &$result, &$c_error]);
      $this->args = [];
      if ($c_error !== ['00000', null, null]) {
        $this->errors[] = $c_error;
        message::insert(
          translation::get('Query error!').br.
          translation::get('sql state: %%_state',        ['state' => translation::get($c_error[0])]).br.
          translation::get('driver error code: %%_code', ['code'  => translation::get($c_error[1])]).br.
          translation::get('driver error text: %%_text', ['text'  => translation::get($c_error[2])]), 'error'
        );
        return null;
      }
      switch (strtoupper(array_values($query)[0])) {
        case 'SELECT': return $result ? $result->fetchAll(pdo::FETCH_CLASS|pdo::FETCH_PROPS_LATE, '\\'.__NAMESPACE__.'\\instance') : null;
        case 'INSERT': return $this->connection->lastInsertId();
        case 'UPDATE': return $result->rowCount();
        case 'DELETE': return $result->rowCount();
        default      : return $result;
      }
    }
  }

  function table($name) {
    if ($name[0] == '~') $name = entity::get(ltrim($name, '~'))->catalog_name;
    if ($this->driver == 'mysql' ) return '`'.$this->table_prefix.$name.'`';
    if ($this->driver == 'sqlite') return '"'.$this->table_prefix.$name.'"';
  }

  function field($name) {
    if (strpos($name, '.') !== false) {
      $parts = explode('.', $name);
      if ($this->driver == 'mysql' ) return $this->table($parts[0]).'.'.($parts[1] === '*' ? '*' : '`'.$parts[1].'`');
      if ($this->driver == 'sqlite') return $this->table($parts[0]).'.'.($parts[1] === '*' ? '*' : '"'.$parts[1].'"'); } else {
      if ($this->driver == 'mysql' ) return $name === '*' ? '*' : '`'.$name.'`';
      if ($this->driver == 'sqlite') return $name === '*' ? '*' : '"'.$name.'"';
    }
  }

  function value($value, $is_emulation = false) {
    if (!$is_emulation) $this->args[] = $value;
    return '?';
  }

  function tables(...$tables) {
    $result = [];
    foreach (is_array($tables[0]) ?
                      $tables[0] : $tables as $c_id => $c_name) {
      $result[$c_id.'_!t'] = $c_name;
    }
    return $result;
  }


  function fields(...$fields) {
    $result = [];
    foreach (is_array($fields[0]) ?
                      $fields[0] : $fields as $c_id => $c_name) {
      $result[$c_id.'_!f'] = $c_name;
    }
    return $result;
  }

  function values(...$values) {
    $result = [];
    foreach (is_array($values[0]) ?
                      $values[0] : $values as $c_id => $c_name) {
      $result[$c_id.'_!v'] = $c_name;
    }
    return $result;
  }

  function attributes_prepare($attributes, $group_op = 'and', $op = '=') {
    $result = [];
    foreach ($attributes as $c_field => $c_value) {
      $result[$c_field] = [
        'field_!f' => $c_field,
        'operator' => $op,
        'value_!v' => $c_value];
      $result[] = $group_op;
    }
    array_pop($result);
    return $result;
  }

  ################
  ### entities ###
  ################

  function entity_install($entity) {
    if ($this->init()) {
      $fields      = [];
      $constraints = [];
      foreach ($entity->fields as $c_name => $c_info) {

      # prepare field name
        $c_field = [
          'name_!f' => $c_name
        ];

      # prepare field type
        switch ($c_info->type) {
          case 'autoincrement':
            if ($this->driver ==  'mysql') $c_field += ['type' => 'integer', 'primary_key' => 'primary key', 'autoincrement' => 'auto_increment'];
            if ($this->driver == 'sqlite') $c_field += ['type' => 'integer', 'primary_key' => 'primary key', 'autoincrement' =>  'autoincrement'];
            break;
          default:
            $c_field['type'] = $c_info->type;
            if (isset($c_info->size)) {
              $c_field['size_begin'] = '(';
              $c_field['size'] = $c_info->size;
              $c_field['size_end'] = ')';
            }
        }

      # prepare field properties
        if (isset($c_info->collate) && $c_info->collate == 'nocase' && $this->driver == 'mysql' ) $c_field += ['collate_begin' => 'collate', 'collate' => 'utf8_general_ci'];
        if (isset($c_info->collate) && $c_info->collate == 'nocase' && $this->driver == 'sqlite') $c_field += ['collate_begin' => 'collate', 'collate' => 'nocase'         ];
        if (isset($c_info->collate) && $c_info->collate == 'binary' && $this->driver == 'mysql' ) $c_field += ['collate_begin' => 'collate', 'collate' => 'utf8_bin'       ];
        if (isset($c_info->collate) && $c_info->collate == 'binary' && $this->driver == 'sqlite') $c_field += ['collate_begin' => 'collate', 'collate' => 'binary'         ];
        if (property_exists($c_info, 'not_null') && $c_info->not_null)                            $c_field['not_null'] = 'not null';
        if (property_exists($c_info, 'null')     && $c_info->null)                                $c_field['null'    ] = 'null';
        if (property_exists($c_info, 'default')) {
          if     ($c_info->default === 0)    $c_field += ['default_begin' => 'default', 'default' => '0'];
          elseif ($c_info->default === null) $c_field += ['default_begin' => 'default', 'default' => 'null'];
          else                               $c_field += ['default_begin' => 'default', 'default' => '\''.$c_info->default.'\''];
        }
        $fields[$c_name] = $c_field;
      }

    # prepare constraints
      $auto_name = $entity->auto_name_get();
      foreach ($entity->constraints as $c_name => $c_info) {
        if ($c_info->fields != [$auto_name => $auto_name]) {
          if ($c_info->type == 'primary') $constraints['constraint-'.$c_name] = ['constraint' => 'CONSTRAINT', 'name_!f' => $entity->catalog_name.'__'.$c_name, 'type' => 'PRIMARY KEY', 'fields_begin' => '(', 'fields_!,' => $this->fields($c_info->fields), 'fields_end' => ')'];
          if ($c_info->type ==  'unique') $constraints['constraint-'.$c_name] = ['constraint' => 'CONSTRAINT', 'name_!f' => $entity->catalog_name.'__'.$c_name, 'type' => 'UNIQUE',      'fields_begin' => '(', 'fields_!,' => $this->fields($c_info->fields), 'fields_end' => ')'];
          if ($c_info->type == 'foreign') $constraints['constraint-'.$c_name] = ['constraint' => 'CONSTRAINT', 'name_!f' => $entity->catalog_name.'__'.$c_name, 'type' => 'FOREIGN KEY', 'fields_begin' => '(', 'fields_!,' => $this->fields($c_info->fields), 'fields_end' => ')', 'references_begin' => 'REFERENCES', 'references_target_!t' => $c_info->references, 'references_fields_begin' => '(', 'references_fields_!,' => $this->fields($c_info->references_fields), 'references_fields_end' => ')', 'on_update_begin' => 'ON UPDATE', 'on_update' => $c_info->on_update ?? 'cascade', 'on_delete_begin' => 'ON DELETE', 'on_delete' => $c_info->on_delete ?? 'cascade'];
        }
      }

    # create entity
      $this->transaction_begin();
      if ($this->driver ==  'mysql') $this->query(['action' => 'SET',    'command' => 'FOREIGN_KEY_CHECKS', '=' => '=', 'value' => '0'  ]);
      if ($this->driver == 'sqlite') $this->query(['action' => 'PRAGMA', 'command' => 'foreign_keys',       '=' => '=', 'value' => 'OFF']);
                                     $this->query(['action' => 'DROP',   'type'    => 'TABLE', 'if_exists' => 'IF EXISTS', 'target_!t' => $entity->catalog_name]);
      if ($this->driver ==  'mysql') $this->query(['action' => 'CREATE', 'type'    => 'TABLE',                             'target_!t' => $entity->catalog_name, 'fields_and_constraints_begin' => '(', 'fields_and_constraints_!,' => $fields + $constraints, 'fields_and_constraints_end' => ')', 'charset_begin' => 'CHARSET', 'charset_condition' => '=', 'charset' => 'utf8']);
      if ($this->driver == 'sqlite') $this->query(['action' => 'CREATE', 'type'    => 'TABLE',                             'target_!t' => $entity->catalog_name, 'fields_and_constraints_begin' => '(', 'fields_and_constraints_!,' => $fields + $constraints, 'fields_and_constraints_end' => ')']);
      if ($this->driver ==  'mysql') $this->query(['action' => 'SET',    'command' => 'FOREIGN_KEY_CHECKS', '=' => '=', 'value' => '1'  ]);
      if ($this->driver == 'sqlite') $this->query(['action' => 'PRAGMA', 'command' => 'foreign_keys',       '=' => '=', 'value' => 'ON' ]);

    # create indexes
      foreach ($entity->indexes as $c_name => $c_info) {
        $this->query([
          'action' => 'CREATE',
          'type' => $c_info->type,
          'name_!f' => $entity->catalog_name.'__'.$c_name,
          'on' => 'ON',
          'target_!t' => $entity->catalog_name,
          'fields_begin' => '(',
          'fields_!,' => $this->fields($c_info->fields),
          'fields_end' => ')'
        ]);
      }

      return $this->transaction_commit();
    }
  }

  function entity_uninstall($entity) {
    if ($this->init()) {
      if ($this->driver ==  'mysql') $this->query(['action' => 'SET',    'command' => 'FOREIGN_KEY_CHECKS', '=' => '=', 'value' => '0'  ]);
      if ($this->driver == 'sqlite') $this->query(['action' => 'PRAGMA', 'command' => 'foreign_keys',       '=' => '=', 'value' => 'OFF']);
      $result =                      $this->query(['action' => 'DROP',   'type' => 'TABLE', 'target_!t' => $entity->catalog_name        ]);
      if ($this->driver ==  'mysql') $this->query(['action' => 'SET',    'command' => 'FOREIGN_KEY_CHECKS', '=' => '=', 'value' => '1'  ]);
      if ($this->driver == 'sqlite') $this->query(['action' => 'PRAGMA', 'command' => 'foreign_keys',       '=' => '=', 'value' => 'ON' ]);
      return $result;
    }
  }

  function instances_select($entity, $params = []) {
    $params += ['join_fields' => [], 'join' => [], 'conditions' => [], 'order' => [], 'limit' => 0, 'offset' => 0];
    if ($this->init()) {
      $query = [
        'action' => 'SELECT',
        'fields_!,' => ['all_!f' => $entity->catalog_name.'.*'] + $params['join_fields'],
        'target_begin' => 'FROM',
        'target_!t' => $entity->catalog_name];
      foreach ($params['join'] as $c_join_id => $c_join_part) {
        $query['join'][$c_join_id] = $c_join_part;
      }
      if (count($params['conditions'])) $query += ['condition_begin' => 'WHERE',    'condition' => $params['conditions']];
      if (count($params['order']))      $query += ['order_begin'     => 'ORDER BY', 'order'     => $params['order']     ];
      if ($params['limit'])             $query += ['limit_begin'     => 'LIMIT',    'limit'     => $params['limit']     ];
      if ($params['offset'])            $query += ['offset_begin'    => 'OFFSET',   'offset'    => $params['offset']    ];
      $result = $this->query($query);
      foreach ($result as $c_instance) {
        $c_instance->entity_name_set($entity->name);
      }
      return $result;
    }
  }

  function instance_select($instance) { # return: null | instance
    if ($this->init()) {
      $entity    = $instance->entity_get();
      $id_fields = $entity->real_id_from_values_get($instance->values_get());
      $result = $this->query([
        'action' => 'SELECT',
        'fields_!,' => ['all_!f' => '*'],
        'target_begin' => 'FROM',
        'target_!t' => $entity->catalog_name,
        'condition_begin' => 'WHERE',
        'condition' => $this->attributes_prepare($id_fields),
        'limit_begin' => 'LIMIT',
        'limit' => 1]);
      if  (isset($result[0])) {
        foreach ($result[0]->values as $c_name => $c_value) {
          $instance->{$c_name} = $c_value;
          $instance->_id_fields_original = $id_fields;
        }
        return $instance;
      }
    }
  }

  function instance_insert($instance) { # return: null | instance | instance + new_id
    if ($this->init()) {
      $entity = $instance->entity_get();
      $values = array_intersect_key($instance->values_get(), $entity->fields_name_get());
      $fields = array_keys($values);
      $auto_name = $entity->auto_name_get();
      $new_id = $this->query([
        'action' => 'INSERT',
        'action_subtype' => 'INTO',
        'target_!t' => $entity->catalog_name,
        'fields_begin' => '(',
        'fields_!,' => $this->fields($fields),
        'fields_end' => ')',
        'values_begin' => 'VALUES (',
        'values_!,' => $this->values($values),
        'values_end' => ')']);
      if ($new_id !== null && $auto_name == null) return $instance;
      if ($new_id !== null && $auto_name != null) {
        $instance->{$auto_name} = $new_id;
        return $instance;
      }
    }
  }

  function instance_update($instance) { # return: null | instance
    if ($this->init()) {
      $entity    = $instance->entity_get();
      $id_fields = $entity->real_id_from_values_get($instance->values_get());
      $values    = array_intersect_key($instance->values_get(), $entity->fields_name_get());
      $row_count = $this->query([
        'action' => 'UPDATE',
        'target_!t' => $entity->catalog_name,
        'fields_and_values_begin' => 'SET',
        'fields_and_values' => $this->attributes_prepare($values, ','),
        'condition_begin' => 'WHERE',
        'condition' => $this->attributes_prepare($instance->_id_fields_original ?: $id_fields)]);
      if ($row_count === 1) {
        $instance->_id_fields_original = $id_fields;
        return $instance;
      }
    }
  }

  function instance_delete($instance) { # return: null | instance + empty(values)
    if ($this->init()) {
      $entity    = $instance->entity_get();
      $id_fields = $entity->real_id_from_values_get($instance->values_get());
      $row_count = $this->query([
        'action' => 'DELETE',
        'target_begin' => 'FROM',
        'target_!t' => $entity->catalog_name,
        'condition_begin' => 'WHERE',
        'condition' => $this->attributes_prepare($id_fields)]);
      if ($row_count === 1) {
        $instance->values_set([]);
        return $instance;
      }
    }
  }

  ###########################
  ### static declarations ###
  ###########################

  static function not_external_properties_get() {
    return ['name' => 'name'];
  }

}}