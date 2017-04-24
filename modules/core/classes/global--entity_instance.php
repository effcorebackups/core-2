<?php

namespace effectivecore {
          use \effectivecore\modules\storage\db;
          class entity_instance {

  public $name;
  public $fields;

  static function get_entities() { # @todo: use cache
    $entities = [];
    foreach (settings_factory::$data['entities'] as $c_entities) {
      foreach ($c_entities as $c_entity) {
        $entities[$c_entity->name] = $c_entity;
      }
    }
    return $entities;
  }

  function __construct($name = '', $fields = null) {
    $this->name = $name;
    if (is_array($fields)) {
      $this->fields = new \StdClass;
      foreach ($fields as $c_key => $_value) {
        $this->fields->{$c_key} = $_value;
      }
    }
  }

  function load() {
    $entities = static::get_entities();
    $data = reset(db::query('SELECT '.implode(', ', array_keys((array)$entities[$this->name]->fields)).' '.
                            'FROM `'.$this->name.'` '.
                            'WHERE id = "'.$this->fields->id.'" '.
                            'LIMIT 1'
    ));
    if (is_array($data)) {
      foreach ($data as $c_key => $c_value) {
        $this->fields->{$c_key} = $c_value;
      }
      return $this;
    }
  }

  function save() {
    $entities = static::get_entities();
    $result = db::query('INSERT INTO `'.$this->name.'` (`'.
                         implode('`, `', array_keys((array)$entities[$this->name]->fields)).'`) VALUES ("'.
                         implode('", "', array_values((array)$this->fields)).'")'
    );
    return $result;
  }

}}