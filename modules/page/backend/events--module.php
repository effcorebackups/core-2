<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\entity_factory as entity;
          use \effectivecore\message_factory as message;
          use \effectivecore\translation_factory as translation;
          use \effectivecore\modules\page\page_factory as page;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    return page::find_and_render();
  }

  static function on_install() {
  # install entities
    foreach (entity::get_all_by_module('page') as $c_entity) {
      if ($c_entity->install()) message::insert(translation::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      message::insert(translation::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}