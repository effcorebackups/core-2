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
    if (!extension_loaded('pdo')) {
      messages::add_new('The PHP PDO extension is not available.', 'warning');
    }
    if (!extension_loaded('pdo_mysql')) {
      $fields['storage/default/driver/mysql']->child_select('default')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'MySQL']), 'warning');
    }
    if (!extension_loaded('pdo_pgsql')) {
      $fields['storage/default/driver/pgsql']->child_select('default')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'PostgreSQL']), 'warning');
    }
    if (!extension_loaded('pdo_sqlite')) {
      $fields['storage/sqlite/driver/sqlite']->child_select('default')->attribute_insert('disabled', 'disabled');
      messages::add_new(translations::get('The PHP PDO driver for %%_name is not available.', ['name' => 'SQLite']), 'warning');
    }
    $db = storages::get('db');
    if (isset($db->driver)) {
      $form->child_delete('storage');
      $form->child_delete('button_install');
      messages::add_new('The system was installed!', 'warning');
    }
  }

  static function on_validate_install($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        if (empty($values['driver'])) {
          $form->add_error(null, 'Driver is not selected!');
          return;
        }
        if (count($form->errors) == 0) {
          switch ($values['driver']) {
            case 'sqlite': $test = storages::get('db')->test($values['driver'], ['file_name' => $values['file_name']]); break;
            default      : $test = storages::get('db')->test($values['driver'], [
                'host_name'     => $values['host_name'],
                'database_name' => $values['database_name'],
                'user_name'     => $values['user_name'],
                'password'      => $values['password']
              ]); break;
          }
          if ($test !== true) {
            messages::add_new('The database is not available with these credentials!', 'error');
            messages::add_new($test['message'], 'error');
            if ($test['code'] == '1049') $form->add_error('storage/default/database_name/default');
            if ($test['code'] == '2002') $form->add_error('storage/default/host_name/default');
            if ($test['code'] == '1045') $form->add_error('storage/default/user_name/default');
            if ($test['code'] == '1045') $form->add_error('storage/default/password/default');
          }
        }
        break;
    }
  }

  static function on_submit_install($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        $params = new \stdClass;
        $params->driver = $values['driver'];
        $params->credentials = new \stdClass;
        $params->credentials->host_name     = $values['host_name'];
        $params->credentials->database_name = $values['database_name'];
        $params->credentials->user_name     = $values['user_name'];
        $params->credentials->password      = $values['password'];
        storages::get('settings')->changes_register_action('core', 'insert', 'storages/storage/storage_sql_dpo', $params);
        storages::rebuild();
        events::start('on_module_install');
        messages::add_new('Modules was installed.');
        $form->child_delete('storage');
        $form->child_delete('button_install');
        break;
      case 'to_front':
        urls::go(urls::get_back_url() ?: '/');
        break;
    }
  }

}}