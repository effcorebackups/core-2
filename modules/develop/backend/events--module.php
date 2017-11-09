<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\develop {
          use \effectivecore\message_factory as message;
          use \effectivecore\entity_factory as entity;
          use \effectivecore\translation_factory as translation;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
  }

  static function on_install() {
    foreach (entity::get_all_by_module('develop') as $c_entity) {
      if ($c_entity->install()) message::add_new(translation::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      message::add_new(translation::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}