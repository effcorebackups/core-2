<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\markup;
          use \effectivecore\table;
          use \effectivecore\pager;
          use \effectivecore\entity_instance;
          use \effectivecore\factory;
          use \effectivecore\url_factory as urls;
          use \effectivecore\entity_factory as entities;
          use \effectivecore\modules\user\user_factory as users;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_admin_roles() {
    $head = [['ID', 'Title', 'Is embed']];
    $body = entities::get('role')->select_set();
    foreach ($body as $c_row) {
      $c_row->is_embed = $c_row->is_embed ? 'Yes' : 'No';
    }
    return new table([], $body, $head);
  }

  static function on_show_admin_users() {
    $pager = new pager();
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $head = [['ID', 'EMail', 'Password hash', 'Created', 'Is embed', 'Actions']];
      $body = entities::get('user')->select_set();
      foreach ($body as $c_row) {
        $c_actions = new markup('ul', ['class' => ['actions' => 'actions']]);
        $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_row->id))->get_full()], 'view') ) );
        $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_row->id.'/edit?'.urls::make_back_part()))->get_full()], 'edit') ) );
        if ($c_row->is_embed != 1) $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/admin/users/delete/'.$c_row->id.'?'.urls::make_back_part()))->get_full()], 'delete') ) );
        $c_row->is_embed = $c_row->is_embed ? 'Yes' : 'No';
        $c_row->password_hash = '*****';
        $c_row->actions = $c_actions;
      }
      return new table([], $body, $head);
    }
  }

  static function on_show_admin_users_delete_n($user_id) {
    $user = (new entity_instance('entities/user/user', ['id' => $user_id]))->select();
    if ($user) {
      if ($user->is_embed == 1) {
        factory::send_header_and_exit('access_denided',
          'This user is embed!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_show_user_n($id) {
    $user = (new entity_instance('entities/user/user', ['id' => $id]))->select();
    if ($user) {
      if ($user->id == users::get_current()->id ||               # owner
                 isset(users::get_current()->roles['admins'])) { # admin
      # get roles
        $roles = [];
        $db_roles = entities::get('relation_role_ws_user')->select_set(['user_id' => $id]);
        if ($db_roles) {
          foreach ($db_roles as $c_role) {
            $roles[] = $c_role->role_id;
          }
        }
      # get values
        $values = $user->get_values();
        $values['roles'] = count($roles) ? implode(', ', $roles) : '-';
        $values['password_hash'] = '*****';
        $values['is_embed'] = $values['is_embed'] ? 'Yes' : 'No';
      # show table
        $head = [['Parameter', 'Value']];
        $body = factory::array_rotate([array_keys($values), array_values($values)]);
        return new table([], $body, $head);
      } else {
        factory::send_header_and_exit('access_denided',
          'Access denided!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_show_user_n_edit($user_id) {
    $user = (new entity_instance('entities/user/user', ['id' => $user_id]))->select();
    if ($user) {
      if (!($user->id == users::get_current()->id ||                # not owner or
                   isset(users::get_current()->roles['admins']))) { # not admin
        factory::send_header_and_exit('access_denided',
          'Access denided!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

}}