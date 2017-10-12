<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\core {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\events_factory as events;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\storage\storages_factory as storages;
          abstract class events_form extends \effectivecore\events_form {

  #####################
  ### form: install ###
  #####################

  static function on_init_install($form, $fields) {
    $driver = $fields['fieldset_default/field_driver']->child_select('default');
    if (!extension_loaded('pdo')) {
      $driver->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO extension is not available.'), 'warning');
    }
    if (!extension_loaded('pdo_mysql')) {
      $driver->child_select('mysql')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'MySQL']), 'warning');
    }
    if (!extension_loaded('pdo_pgsql')) {
      $driver->child_select('pgsql')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'PostgreSQL']), 'warning');
    }
    if (!extension_loaded('pdo_sqlite')) {
      $driver->child_select('sqlite')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'SQLite']), 'warning');
    }
    $db = storages::get('db');
    if (isset($db->driver) &&
        isset($db->host_name) &&
        isset($db->database_name) &&
        isset($db->user_name)) {
      $form->child_delete('fieldset_default');
      $form->child_delete('button_install');
      messages::add_new('The system was installed!', 'warning');
    }
  }

  static function on_validate_install($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        if (count($form->errors) == 0) {
          $credentials = [
            'driver'        => $values['driver'],
            'host_name'     => $values['host_name'],
            'database_name' => $values['database_name'],
            'user_name'     => $values['user_name'],
            'password'      => $values['password']];
          $result = storages::get('db')->test($credentials);
          if ($result !== true) {
            messages::add_new('The database is not available with these credentials!', 'error');
            messages::add_new($result['message'], 'error');
            if ($result['code'] == '1049') $form->add_error('fieldset_default/field_database_name/default');
            if ($result['code'] == '2002') $form->add_error('fieldset_default/field_host_name/default');
            if ($result['code'] == '1045') $form->add_error('fieldset_default/field_user_name/default');
            if ($result['code'] == '1045') $form->add_error('fieldset_default/field_password/default');
          }
        }
        break;
    }
  }

  static function on_submit_install($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        $credentials = [
          'driver'        => $values['driver'],
          'host_name'     => $values['host_name'],
          'database_name' => $values['database_name'],
          'user_name'     => $values['user_name'],
          'password'      => $values['password']];
        storages::get('settings')->changes_register_action('core', 'insert', 'storages/storage/storage_sql_dpo', (object)$credentials);
        storages::rebuild();
        events::start('on_module_install');
        messages::add_new('Modules was installed.');
        $form->child_delete('fieldset_default');
        $form->child_delete('button_install');
        break;
      case 'cancel':
        urls::go(urls::get_back_url() ?: '/');
        break;
    }
  }

}}