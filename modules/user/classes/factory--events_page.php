<?php

namespace effectivecore\modules\user {
          use \effectivecore\url;
          use \effectivecore\markup;
          use \effectivecore\table;
          use \effectivecore\pager;
          use \effectivecore\entity_instance;
          use \effectivecore\factory;
          use \effectivecore\settings_factory;
          use \effectivecore\entity_factory;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\modules\page\page_factory as page;
          use \effectivecore\modules\user\user_factory as user;
          abstract class events_page_factory extends \effectivecore\events_page_factory {

  static function on_show_admin_roles() {
    $head = [['ID', 'Title', 'Is embed']];
    $body = entity_factory::get_entity('role')->select_set();
    foreach ($body as $c_row) {
      $c_row->is_embed = $c_row->is_embed ? 'Yes' : 'No';
    }
    page::add_element(
      new table([], $body, $head)
    );
  }

  static function on_show_admin_users() {
    $items_per_page = settings_factory::$data['pages']['user']['page_admin_users']->constants['items_per_page'];
    $pager = new pager();
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $head = [['ID', 'EMail', 'Password hash', 'Created', 'Is embed', 'Actions']];
      $body = entity_factory::get_entity('user')->select_set();
      foreach ($body as $c_row) {
        $c_actions = new markup('ul', ['class' => 'actions']);
        $c_actions->add_child( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_row->id))->get_full()], 'view') ) );
        $c_actions->add_child( new markup('li', [], new markup('a', ['href' => (new url('/user/'.$c_row->id.'/edit?'.urls::get_back_part()))->get_full()], 'edit') ) );
        if ($c_row->is_embed != 1) $c_actions->add_child( new markup('li', [], new markup('a', ['href' => (new url('/admin/users/delete/'.$c_row->id.'?'.urls::get_back_part()))->get_full()], 'delete') ) );
        $c_row->is_embed = $c_row->is_embed ? 'Yes' : 'No';
        $c_row->password_hash = '*****';
        $c_row->actions = $c_actions;
      }
      page::add_element(new table([], $body, $head));
      page::add_element($pager);
    }
  }

  static function on_show_admin_users_delete_n($user_id) {
    $user = (new entity_instance('entities/user/user', ['id' => $user_id]))->select();
    if ($user) {
      if ($user->get_value('is_embed') == 1) {
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
      if ($user->get_value('id') == user::$current->id || # owner
          isset(user::$current->roles['admins'])) {       # admin
        $values = $user->get_values();
        $values['password_hash'] = '*****';
        $values['is_embed'] = $values['is_embed'] ? 'Yes' : 'No';
        $head = [['Parameter', 'Value']];
        $body = factory::array_rotate([array_keys($values), array_values($values)]);
        page::add_element(new table([], $body, $head));
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
      if (!($user->get_value('id') == user::$current->id || # not owner or
            isset(user::$current->roles['admins']))) {      # not admin
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