<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Tree extends Node {

    public $template = 'tree';
    public $attributes = [
        'role' => 'tree'];
    public $id;
    public $description;
    public $access;
    public $origin = 'nosql'; # nosql | sql | dynamic
    public $manage_mode; # null | decorate | rearrange

    function __construct($description = null, $id = null, $access = null, $attributes = [], $weight = +0) {
        if ($description) $this->description = $description;
        if ($id         ) $this->id          = $id;
        if ($access     ) $this->access      = $access;
        parent::__construct($attributes, [], $weight);
    }

    function build() {
        if (!$this->is_builded) {
            Event::start('on_tree_build_before', $this->id, ['tree' => &$this]);
            $this->attribute_insert('data-id'         , $this->id         , 'attributes', true);
            $this->attribute_insert('data-manage-mode', $this->manage_mode, 'attributes', true);
            foreach (Tree_item::select_all_by_id_tree($this->id) as $c_item) {
                if ($c_item->id_tree   === $this->id &&
                    $c_item->id_parent === null) {
                    $this->child_insert($c_item, $c_item->id);
                    $c_item->build(); }}
            Event::start('on_tree_build_after', $this->id, ['tree' => &$this]);
            $this->is_builded = true;
        }
    }

    function render() {
        if (Access::check($this->access)) {
            static::init();
            $this->build();
            return parent::render();
        }
    }

    ###########################
    ### static declarations ###
    ###########################

    protected static $cache;
    protected static $is_init_nosql = false;
    protected static $is_init___sql = false;

    static function cache_cleaning() {
        static::$cache         = null;
        static::$is_init_nosql = false;
        static::$is_init___sql = false;
    }

    static function init() {
        if (!static::$is_init_nosql) {
             static::$is_init_nosql = true;
            foreach (Storage::get('data')->select_array('trees') as $c_module_id => $c_trees) {
                foreach ($c_trees as $c_row_id => $c_tree) {
                    if (isset(static::$cache[$c_tree->id])) Console::report_about_duplicate('trees', $c_tree->id, $c_module_id, static::$cache[$c_tree->id]);
                              static::$cache[$c_tree->id] = $c_tree;
                              static::$cache[$c_tree->id]->origin = 'nosql';
                              static::$cache[$c_tree->id]->module_id = $c_module_id;
                }
            }
        }
    }

    static function init_sql($id = null) {
        if ($id && isset(static::$cache[$id])) return;
        if (!static::$is_init___sql) {
             static::$is_init___sql = true;
            foreach (Entity::get('tree')->instances_select() as $c_instance) {
                $c_tree = new static(
                    $c_instance->description,
                    $c_instance->id,
                    $c_instance->access,
                    Widget_Attributes::value_to_attributes($c_instance->attributes) ?? [], 0);
                static::$cache[$c_tree->id] = $c_tree;
                static::$cache[$c_tree->id]->origin = 'sql';
                static::$cache[$c_tree->id]->module_id = $c_instance->module_id;
            }
        }
    }

    static function select_all($origin = null) {
        if ($origin === 'nosql') {static::init();                    }
        if ($origin === 'sql'  ) {                static::init_sql();}
        if ($origin ===  null  ) {static::init(); static::init_sql();}
        $result = static::$cache ?? [];
        if ($origin)
            foreach ($result as $c_id => $c_item)
                if ($c_item->origin !== $origin)
                    unset($result[$c_id]);
        return $result;
    }

    static function select($id) {
        static::init    (   );
        static::init_sql($id);
        return static::$cache[$id] ?? null;
    }

    static function insert($description = null, $id = null, $access = null, $attributes = [], $weight = +0, $module_id = null) {
        static::init    (   );
        static::init_sql($id);
        $new_tree = new static($description, $id, $access, $attributes, $weight);
               static::$cache[$id] = $new_tree;
               static::$cache[$id]->origin = 'dynamic';
               static::$cache[$id]->module_id = $module_id;
        return static::$cache[$id];
    }

    static function delete($id, $with_items = true) {
        static::init    (   );
        static::init_sql($id);
        if ($with_items)
            foreach (Tree_item::select_all_by_id_tree($id) as $c_item)
                Tree_item::delete($c_item->id, $id);
        unset(static::$cache[$id]);
    }

}
