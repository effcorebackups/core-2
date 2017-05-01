<?php

namespace effectivecore {
          use \effectivecore\timer_factory as timer;
          use \effectivecore\console_factory as console;
          use \effectivecore\modules\storage\storage_factory as storage;
          class storage_instance {

  public $id;
  public $connection;
  public $directory_name;
  public $host_name;
  public $user_name;
  public $password;
  public $driver;
  public $is_init = false;
  public $queries = [];

  function init() {
    if (empty($this->is_init)) {
      try {
        $this->connection = new \PDO($this->driver.':host='.
          $this->host_name.';dbname='.
          $this->directory_name,
          $this->user_name,
          $this->password
        );
        $this->is_init = true;
        console::set_log('', 'The database was initialized on first request.', 'Queries');
      } catch (\PDOException $e) {
        factory::send_header_and_exit('access_denided',
          'Database is unavailable!'
        );
      }
    }
  }

  function query($query) {
    $this->queries[] = $query;
    timer::tap('query_'.count($this->queries));
    $result = $this->connection->query($query);
    timer::tap('query_'.count($this->queries));
    console::set_log(
      timer::get_period('query_'.count($this->queries), 0, 1).' sec.', $query, 'Queries'
    );
    switch (substr($query, 0, 6)) {
      case 'SELECT':
        $i = 0;
        $return = [];
        if ($result) {
          while ($row = $result->fetch(\PDO::FETCH_OBJ)) {
            $return[isset($row->id) ? $row->id : $i++] = $row;
          }
        }
        return $return;
      case 'UPDATE': return $result->rowCount();
      case 'DELETE': return $result->rowCount();
      case 'INSERT': return $this->connection->lastInsertId();
    }
  }

  function install_entity($entity) {
    $this->init();
    $field_desc = [];
    foreach ($entity->fields as $c_name => $c_info) {
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
    foreach ($entity->indexes as $c_info) {
      $field_desc[] = $c_info->type.' (`'.implode('`, `', $c_info->fields).'`)';
    }
    $this->query(
      'CREATE TABLE `'.$entity->name.'` ('.implode(', ', $field_desc).') '.
      'default charset='.$entity->charset.';'
    );
  }

  function uninstall_entity($entity) {
    $this->init();
    $this->query('DROP TABLE `'.$entity->name.'`;');
  }

  function select_instance_set($entity, $conditions = [], $order = [], $count = 0, $offset = 0) {
    $this->init();
    return $this->query(
      'SELECT `'.implode('`, `', array_keys($entity->fields)).'` '.
      'FROM `'.$entity->name.'`'.
      (count($conditions) ? ' WHERE '.factory::data_to_attr($conditions, ' and ') : ''). # @todo: add "`"
      (count($order)      ? ' ORDER BY `'.str_replace('!', ' DESC ', implode('`, `', $order)).'`' : '').
      ($count             ? ' LIMIT ' .$count  : '').
      ($offset            ? ' OFFSET '.$offset : '').';'
    );
  }

  function select_instance($instance) {
    $this->init();
    $result = reset($this->query(
      'SELECT `'.implode('`, `', array_keys((array)$instance->fields)).'` '.
      'FROM `'.$instance->name.'` '.
      'WHERE `id` = "'.$instance->fields->id.'";'
    ));
    if (isset($result->id) && $result->id === $instance->fields->id) {
      foreach ($result as $prop => $value) {
        $instance->fields->{$prop} = $value;
      }
      return $instance;
    }
  }

  function insert_instance($instance) {
    $this->init();
    return $instance->fields->id = $this->query(
      'INSERT INTO `'.$instance->name.'` (`'.implode('`, `', array_keys((array)$instance->fields)).'`) '.
      'VALUES ("'.implode('", "', array_values((array)$instance->fields)).'");'
    );
  }

  function update_instance($instance) {
    $this->init();
    return $this->query(
      'UPDATE `'.$instance->name.'` '.
      'SET '.factory::data_to_attr($instance->fields, ', ').' '. # @todo: add "`"
      'WHERE `id` = "'.$instance->fields->id.'";'
    );
  }

  function delete_instance($instance) {
    $this->init();
    return $this->query(
      'DELETE FROM `'.$instance->name.'` '.
      'WHERE `id` = "'.$instance->fields->id.'";'
    );
  }

  function load_data($name, $fields, $conditions) {
    $this->init();
    return reset($this->query(
      'SELECT `'.implode('`, `', $fields).'` '.
      'FROM `'.$name.'` '.
      'WHERE '.factory::data_to_attr($conditions, ' and ').';'
    ));
  }

}}