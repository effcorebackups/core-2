<?php

  ##################################################################
  ### Copyright © 2017—2019 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          class selection extends node
          implements has_external_cache {

  public $view_type = 'table';
  public $title;
  public $fields;
  public $conditions;
  public $order;
  public $count;
  public $offset;

  function __construct($title = '', $view_type = null, $weight = 0) {
    if ($title)     $this->title     = $title;
    if ($view_type) $this->view_type = $view_type;
    parent::__construct([], [], $weight);
  }

  function build() {
    $markup = null;
    $used_entities = [];
    $used_storages = [];
    foreach ($this->fields as $c_field) {
      $c_entity = entity::get($c_field->entity_name, false);
      $used_entities[$c_entity->name]       = $c_entity->name;
      $used_storages[$c_entity->storage_id] = $c_entity->storage_id;
    }
  # get data from storage
    if (count($used_entities) == 1 &&
        count($used_storages) == 1) {
      $entity    = entity::get(reset($used_entities));
      $instances = entity::get(reset($used_entities))->instances_select();
      $idkeys    = $entity->key_primary_get() +
                   $entity->keys_unique_get();
    }
  # make markup
    if (!empty($entity)) {
      // $pager = new pager();
      // if ($pager->has_error) {
      //   core::send_header_and_exit('page_not_found');
      // }
      switch ($this->view_type) {
        case 'table':
          $thead = [];
          $tbody = [];
        # make thead
          foreach ($this->fields as $c_field) {
            $thead[] = new table_head_row_cell(['class' => [$c_field->field_name => $c_field->field_name]],
              $entity->fields[$c_field->field_name]->title
            );
          }
        # make tbody
          foreach ($instances as $c_instance) {
            if (reset($idkeys)) {
              $id_name = reset($idkeys);
              if (empty($c_instance->is_embed)) {
                $c_action_list = new control_actions_list();
                $c_action_list->action_add('/manage/instances/select/'.$entity->name.'/'.$c_instance->{$id_name}, 'select');
                $c_action_list->action_add('/manage/instances/update/'.$entity->name.'/'.$c_instance->{$id_name}, 'update');
                $c_action_list->action_add('/manage/instances/delete/'.$entity->name.'/'.$c_instance->{$id_name}, 'delete');
              }
            }
            $c_tbody_row = [];
            foreach ($this->fields as $c_field) {
              $c_type = $entity->fields[$c_field->field_name]->type;
              $c_value = $c_instance->{$c_field->field_name};
              if ($c_type == 'date')     $c_value = locale::format_date    ($c_value);
              if ($c_type == 'time')     $c_value = locale::format_time    ($c_value);
              if ($c_type == 'datetime') $c_value = locale::format_datetime($c_value);
              if ($c_type == 'bool')     $c_value = $c_value ? 'Yes' : 'No';
              $c_tbody_row[] = new table_body_row_cell(['class' => [$c_field->field_name => $c_field->field_name]], $c_value);
            }
            $tbody[] = $c_tbody_row;
          }
          return new table([], $tbody, [$thead]);
      }
    }
  }

  function field_insert($entity_name, $field_name) {
    $this->fields[$entity_name.'.'.$field_name] = (object)[
      'entity_name' => $entity_name,
      'field_name'  => $field_name
    ];
  }

  function render_self() {
    return $this->title ? (new markup('h2', [], $this->title))->render() : '';
  }

  function render() {
    $this->child_delete('markup');
    $this->child_insert($this->build(), 'markup');
    return parent::render();
  }

  ###########################
  ### static declarations ###
  ###########################

  static protected $cache;

  static function not_external_properties_get() {
    return [];
  }

  static function init() {
    foreach (storage::get('files')->select('selections') as $c_module_id => $c_selections) {
      foreach ($c_selections as $c_row_id => $c_selection) {
        if (isset(static::$cache[$c_row_id])) console::log_about_duplicate_add('selection', $c_row_id);
        static::$cache[$c_row_id] = $c_selection;
        static::$cache[$c_row_id]->module_id = $c_module_id;
      }
    }
  }

  static function get($row_id, $load = true) {
    if (static::$cache == null) static::init();
    if (static::$cache[$row_id] instanceof external_cache && $load)
        static::$cache[$row_id] = static::$cache[$row_id]->external_cache_load();
    return static::$cache[$row_id] ?? null;
  }

}}