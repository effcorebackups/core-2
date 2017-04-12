<?php

namespace effectivecore\modules\user {
          use \effectivecore\factory;
          use \effectivecore\markup;
          use \effectivecore\table;
          use \effectivecore\node;
          use \effectivecore\html_pager;
          use \effectivecore\urls;
          use \effectivecore\url;
          use \effectivecore\modules\page\page;
          abstract class events_page extends \effectivecore\events_page {

  static function on_show_admin_roles() {
    $data = table_role::select(['id', 'title', 'is_embed'], [], ['is_embed!']);
    foreach ($data as &$c_row) $c_row['is_embed'] = $c_row['is_embed'] ? 'Yes' : 'No';
    page::add_element(
      new table([], $data, [['ID', 'Title', 'Is embed']])
    );
  }

  static function on_show_admin_users() {
    $total_items = table_user::select_one(['count(id)'])['count(id)'];
    $items_per_page = 50; // # @todo: settings::$data['admin_users']['constants']['items_per_page'];
    $pager = new html_pager([], $total_items, $items_per_page);
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $db_user = table_user::select(['id', 'email', 'created', 'is_locked'], [], ['id'], $items_per_page, ($pager->c_page_num - 1) * $items_per_page);
      $url_back = urlencode(urls::$current->get_full());
      foreach ($db_user as &$c_row) {
        $c_row['actions'] = new markup('div', ['class' => 'actions']);
        $c_row['actions']->add_child( new markup('a', ['href' => (new url('/user/'.$c_row['id']))->get_full()], 'view') );
        $c_row['actions']->add_child( new markup('a', ['href' => (new url('/user/'.$c_row['id'].'/edit?back='.$url_back))->get_full()], 'edit') );
        if (empty($c_row['is_locked'])) $c_row['actions']->add_child( new markup('a', ['href' => (new url('/admin/users/delete/'.$c_row['id'].'?back='.$url_back))->get_full()], 'delete') );
        $c_row['is_locked'] = $c_row['is_locked'] ? 'Yes' : 'No';
      }
      $table = new table([], $db_user, [['ID', 'EMail', 'Created', 'Is embed', '']]);
      page::add_element($table);
      page::add_element($pager);
    }
  }

  static function on_show_admin_users_delete_n($user_id) {
    $db_user = table_user::select_one(['id', 'email', 'is_locked'], ['id' => $user_id]);
    if (isset($db_user['id']) == false)                               factory::send_header_and_exit('not_found', 'User not found!');
    if (isset($db_user['is_locked']) && $db_user['is_locked'] == '1') factory::send_header_and_exit('access_denided', 'This user is locked!');
  }

  static function on_show_user_n($user_id) {
    $db_user = table_user::select_one(['*'], ['id' => $user_id]);
    $db_user_roles = table_role_by_user::select(['role_id'], ['user_id' => $user_id]);
    if ($db_user) {
      if ($db_user['id'] == user::$current->id || isset(user::$current->roles['admins'])) {
        unset($db_user['password_hash'], $db_user['is_locked']);
        page::add_element(new table([], factory::array_rotate([array_keys($db_user), array_values($db_user)]), [['Parameter', 'Value']]));
        page::add_element(new table([], $db_user_roles, [['Roles']]));
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
    $db_user = table_user::select_one(['*'], ['id' => $user_id]);
    if (isset($db_user['id']) == false)                                                                             factory::send_header_and_exit('not_found', 'User not found!');
    if (isset($db_user['id']) && !($db_user['id'] == user::$current->id || isset(user::$current->roles['admins']))) factory::send_header_and_exit('access_denided', 'Access denided!');
  }

}}