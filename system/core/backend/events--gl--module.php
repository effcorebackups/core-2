<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effcore {
          abstract class events_module {

  static function on_start() {}

  static function on_install($module_id = null) {
    if ($module_id) {
      $entities = entity::get_all_by_module($module_id);
      $instances = instance::get_by_module($module_id);
    # install entities
      if ($entities) {
        foreach ($entities as $c_entity) {
          if ($c_entity->install())
               message::insert(translation::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
          else message::insert(translation::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
        }
      }
    # insert instances
      if ($instances) {
        foreach ($instances as $c_instance) {
          if ($c_instance->insert())
               message::insert(translation::get('Instances of entity %%_name was added.',     ['name' => $c_entity->get_name()]));
          else message::insert(translation::get('Instances of entity %%_name was not added!', ['name' => $c_entity->get_name()]), 'error');
        }
      }
    }
  }

}}