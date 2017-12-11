<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\factory as factory;
          use \effectivecore\entity_factory as entity;
          use \effectivecore\message_factory as message;
          use \effectivecore\instance_factory as instance;
          use \effectivecore\modules\user\user_factory as user;
          use \effectivecore\translation_factory as translation;
          use \effectivecore\modules\user\session_factory as session;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    $session = session::select();
    if ($session &&
        $session->id_user) {
      user::init($session->id_user);
    }
  }

  static function on_install() {
  # install entities
    foreach (entity::select_all_by_module('user') as $c_entity) {
      if ($c_entity->install()) message::insert(translation::select('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      message::insert(translation::select('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  # insert instances
    foreach (instance::select_by_module('user') as $c_instance) {
      if ($c_instance->entity_name == 'user') $c_instance->created = factory::datetime_get();
      if ($c_instance->insert()) message::insert(translation::select('Instances of entity %%_name was added.',     ['name' => $c_entity->get_name()]));
      else                       message::insert(translation::select('Instances of entity %%_name was not added!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}