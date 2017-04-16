<?php

namespace effectivecore {
          use \effectivecore\modules\data\db;
          class entity {

  function install() {
    $field_sql = [];
    foreach ($this->fields as $c_name => $c_info) {
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
    return db::query('CREATE TABLE `'.$this->name.'` ('.(isset($this->primary_keys) ?
      implode(', ', $field_sql).', PRIMARY KEY (`'.implode('`, `', $this->primary_keys).'`)' :
      implode(', ', $field_sql)
    ).') default charset='.$this->charset.';');
  }

  function uninstall() {
    db::query('DROP TABLE `'.$this->name.'`;');
  }

  function select_instance($id) {
    return (object)reset(db::query('SELECT '.implode(', ', array_keys($this->fields)).' '.
                                   'FROM `'.$this->name.'` '.
                                   'WHERE id = '.$id.' '.
                                   'LIMIT 1'));
  }

  function update_instance() {
  }

  function delete_instance() {
  }

}}