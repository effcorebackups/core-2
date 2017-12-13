<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore\modules\user {
          use \effectivecore\url as url;
          use \effectivecore\table as table;
          use \effectivecore\pager as pager;
          use \effectivecore\locale as locale;
          use \effectivecore\markup as markup;
          use \effectivecore\entity as entity;
          use \effectivecore\instance as instance;
          use \effectivecore\factory as factory;
          use \effectivecore\modules\user\user as user;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_block_roles($page) {
    $block = new markup('x-block', ['id' => 'admin_roles']);
    $thead = [['ID', 'Title', 'Is embed']];
    $tbody = entity::get('role')->select_instances();
    foreach ($tbody as $c_row) {
      $c_row->is_embed = $c_row->is_embed ? 'Yes' : 'No';
    }
    $block->child_insert(new table([], $tbody, $thead));
    return $block;
  }

  static function on_show_block_users($page) {
    $pager = new pager();
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $block = new markup('x-block', ['id' => 'admin_users']);
      $thead = [['ID', 'EMail', 'Nick', 'Created', 'Is embed', 'Actions']];
      $tbody = [];
      foreach (entity::get('user')->select_instances() as $c_user) {
        $c_actions = new markup('ul', ['class' => ['actions' => 'actions']]);
                                    $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_user->id))->get_full()], 'view') ) );
                                    $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_user->id.'/edit?'.url::make_back_part()))->get_full()], 'edit') ) );
        if ($c_user->is_embed != 1) $c_actions->child_insert( new markup('li', [], new markup('a', ['href' => (new url('/admin/users/delete/'.$c_user->id.'?'.url::make_back_part()))->get_full()], 'delete') ) );
        $tbody[] = [
          $c_user->id,
          $c_user->email,
          $c_user->nick.' ',
          locale::format_datetime($c_user->created),
          $c_user->is_embed ? 'Yes' : 'No',
          $c_actions
        ];
      }
      $block->child_insert(new table([], $tbody, $thead));
      return $block;
    }
  }

  static function on_show_block_user_info($page, $id) {
    $user = (new instance('user', ['id' => $id]))->select();
    if ($user) {
      if ($user->id == user::get_current()->id ||               # owner
                 isset(user::get_current()->roles['admins'])) { # admin
        $block = new markup('x-block', ['id' => 'user_info']);
      # get roles
        $roles = [];
        $storage_roles = entity::get('relation_role_ws_user')->select_instances(['id_user' => $id]);
        if ($storage_roles) {
          foreach ($storage_roles as $c_role) {
            $roles[] = $c_role->id_role;
          }
        }
      # get values
        $values = $user->get_values();
        $values['roles'] = count($roles) ? implode(', ', $roles) : '-';
        $values['created'] = locale::format_datetime($values['created']);
        $values['password_hash'] = '*****';
        $values['is_embed'] = $values['is_embed'] ? 'Yes' : 'No';
      # show table
        $thead = [['Parameter', 'Value']];
        $tbody = factory::array_rotate([array_keys($values), array_values($values)]);
        $block->child_insert(new table([], $tbody, $thead));
        return $block;
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

}}