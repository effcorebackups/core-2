<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\core {
          use const \effectivecore\br;
          use \effectivecore\url_factory as url;
          use \effectivecore\event_factory as event;
          use \effectivecore\message_factory as message;
          use \effectivecore\translation_factory as translation;
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class events_form extends \effectivecore\events_form {

  ##########################
  ### form: installation ###
  ##########################

  static function on_init_installation($form, $fields) {
    if (!extension_loaded('pdo')) {
      message::insert('PHP PDO extension is not available.', 'warning');
    }
    if (!extension_loaded('pdo_mysql')) {
      $fields['storage/is_mysql']->child_select('element')->attribute_insert('disabled', 'disabled');
      message::insert(translation::get('PHP PDO driver for %%_name is not available.', ['name' => 'MySQL']), 'warning');
    }
    if (!extension_loaded('pdo_sqlite')) {
      $fields['storage/is_sqlite']->child_select('element')->attribute_insert('disabled', 'disabled');
      message::insert(translation::get('PHP PDO driver for %%_name is not available.', ['name' => 'SQLite']), 'warning');
    }
    $main = storage::get('main');
    if (isset($main->driver)) {
      $form->child_delete('storage');
      $form->child_delete('button_install');
      message::insert('Installation is not available because storage credentials was setted!', 'warning');
    }
  }

  static function on_validate_installation($form, $fields, &$values) {
    static::on_validate($form, $fields, $values);
    switch ($form->clicked_button_name) {
      case 'install':
        if (empty($values['driver'][0])) {
          $form->add_error(null, 'Driver is not selected!');
          return;
        }
        if (count($form->errors) == 0) {
          switch ($values['driver'][0]) {
            case 'sqlite':
              $test = storage::get('main')->test($values['driver'][0], (object)[
                'file_name' => $values['file_name'][0]
              ]);
              if ($test !== true) {
                $form->add_error('storage/sqlite/file_name/element');
                $form->add_error(null, translation::get('Storage is not available with these credentials!').br.
                                       translation::get('Message from storage: %%_message', ['message' => strtolower($test['message'])]));
              }
              break;
            default:
              $test = storage::get('main')->test($values['driver'][0], (object)[
                'host_name'    => $values['host_name'][0],
                'port'         => $values['port'][0],
                'storage_name' => $values['storage_name'][0],
                'user_name'    => $values['user_name'][0],
                'password'     => $values['password'][0]
              ]);
              if ($test !== true) {
                $form->add_error('storage/mysql/storage_name/element');
                $form->add_error('storage/mysql/host_name/element');
                $form->add_error('storage/mysql/port/element');
                $form->add_error('storage/mysql/user_name/element');
                $form->add_error('storage/mysql/password/element');
                $form->add_error(null, translation::get('Storage is not available with these credentials!').br.
                                       translation::get('Message from storage: %%_message', ['message' => strtolower($test['message'])]));
              }
              break;
          }
        }
        break;
    }
  }

  static function on_submit_installation($form, $fields, &$values) {
    switch ($form->clicked_button_name) {
      case 'install':
        switch ($values['driver'][0]) {
          case 'sqlite':
            $params = new \stdClass;
            $params->driver = $values['driver'][0];
            $params->credentials = new \stdClass;
            $params->credentials->file_name    = $values['file_name'][0];
            $params->table_prefix              = $values['table_prefix'][0];
            break;
          default:
            $params = new \stdClass;
            $params->driver = $values['driver'][0];
            $params->credentials = new \stdClass;
            $params->credentials->host_name    = $values['host_name'][0];
            $params->credentials->port         = $values['port'][0];
            $params->credentials->storage_name = $values['storage_name'][0];
            $params->credentials->user_name    = $values['user_name'][0];
            $params->credentials->password     = $values['password'][0];
            $params->table_prefix              = $values['table_prefix'][0];
            break;
        }
        storage::get('settings')->changes_register_action('core', 'insert', 'storages/storage/storage_sql_dpo', $params);
        storage::rebuild();
        event::start('on_module_install');
        message::insert('Modules was installed.');
        $form->child_delete('storage');
        $form->child_delete('button_install');
        break;
      case 'to_front':
        url::go(url::get_back_url() ?: '/');
        break;
    }
  }

}}