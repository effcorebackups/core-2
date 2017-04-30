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
  public $is_init;
  public $queries;

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
      timer::get_period('query_'.count($this->queries), 0, 1).' sec.', $query, 'queries'
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
    $field_sql = [];
    foreach ($entity->fields as $c_name => $c_info) {
      $c_prop = [$c_info->type.(isset($c_info->size) ? '('.$c_info->size.')' : '')];
      if (property_exists($c_info, 'unsigned')       && $c_info->unsigned)       $c_prop[] = 'unsigned';
      if (property_exists($c_info, 'auto_increment') && $c_info->auto_increment) $c_prop[] = 'auto_increment';
      if (property_exists($c_info, 'not_null')       && $c_info->not_null)       $c_prop[] = 'not null';
      if (property_exists($c_info, 'null')           && $c_info->null)           $c_prop[] = 'null';
      if (property_exists($c_info, 'default') && is_string($c_info->default))    $c_prop[] = 'default "'.$c_info->default.'"';
      if (property_exists($c_info, 'default') && is_int($c_info->default))       $c_prop[] = 'default "'.$c_info->default.'"';
      if (property_exists($c_info, 'default') && is_null($c_info->default))      $c_prop[] = 'default null';
      $field_sql[] = '`'.$c_name.'` '.implode(' ', $c_prop);
    }
    return $this->query('CREATE TABLE `'.$entity->name.'` ('.(isset($entity->primary_keys) ?
      implode(', ', $field_sql).', PRIMARY KEY (`'.implode('`, `', $entity->primary_keys).'`)' :
      implode(', ', $field_sql)
    ).') default charset='.$entity->charset.';');
  }

  function uninstall_entity($entity) {
    $this->init();
    $this->query('DROP TABLE `'.$entity->name.'`;');
  }

  function select_entity($entity, $conditions = [], $order = [], $count = 0, $offset = 0) {
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

  function insert_entity($instance) {
    $this->init();
    return $this->query('INSERT INTO `'.$instance->name.'` (`'.implode('`, `', array_keys((array)$instance->fields)).'`) '.
                        'VALUES ("'.implode('", "', array_values((array)$instance->fields)).'")');
  }

  function update_entity($instance, $conditions = []) {
    $this->init();
    return $this->query(
      'UPDATE '.$instance->name.' '.
      'SET '.  factory::data_to_attr(array_keys($instance->fields), ', ').' '.
      'WHERE '.factory::data_to_attr($conditions, ' and ')
    );
  }

  function delete_entity($instance, $conditions) {
    $this->init();
    return $this->query(
      'DELETE FROM '.$instance->name.' '.
      'WHERE '.factory::data_to_attr($conditions, ' and ')
    );
  }

}}