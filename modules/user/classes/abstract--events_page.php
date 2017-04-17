<?php

namespace effectivecore\modules\user {
          use \effectivecore\factory;
          use \effectivecore\entity_instance;
          use \effectivecore\settings;
          use \effectivecore\markup;
          use \effectivecore\table;
          use \effectivecore\table_body_row_cell;
          use \effectivecore\pager;
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
    $pager = new pager();
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $db_user = table_user::select(['id', 'email', 'created', 'is_locked'], [], ['id'], $items_per_page, ($pager->get_current_page_num() - 1) * $items_per_page);
      $url_back = urlencode(urls::$current->get_full());
      foreach ($db_user as &$c_row) {
        $c_row['actions'] = new table_body_row_cell(['class' => 'actions']);
        $c_row['actions']->add_child( new markup('a', ['href' => (new url('/user/'.$c_row['id']))->get_full()], 'view') );
        $c_row['actions']->add_child( new markup('a', ['href' => (new url('/user/'.$c_row['id'].'/edit?back='.$url_back))->get_full()], 'edit') );
        if (empty($c_row['is_locked'])) $c_row['actions']->add_child( new markup('a', ['href' => (new url('/admin/users/delete/'.$c_row['id'].'?back='.$url_back))->get_full()], 'delete') );
        $c_row['is_locked'] = $c_row['is_locked'] ? 'Yes' : 'No';
      }
      $table = new table([], $db_user, [['ID', 'EMail', 'Created', 'Is embed', 'Actions']]);
      page::add_element($table);
      page::add_element($pager);
    }
  }

  static function on_show_admin_users_delete_n($user_id) {
    $db_user = table_user::select_one(['id', 'email', 'is_locked'], ['id' => $user_id]);
    if (isset($db_user['id']) == false)                               factory::send_header_and_exit('not_found', 'User not found!');
    if (isset($db_user['is_locked']) && $db_user['is_locked'] == '1') factory::send_header_and_exit('access_denided', 'This user is locked!');
  }

  static function on_show_user_n($id) {
    $user = (new entity_instance('user', ['id' => $id]))->load();
    if ($user) {
      if ($user->fields->id == user::$current->id || isset(user::$current->roles['admins'])) {
        unset($user->fields->password_hash);
        unset($user->fields->is_locked);
        page::add_element(new table([], factory::array_rotate([array_keys((array)$user->fields), array_values((array)$user->fields)]), [['Parameter', 'Value']]));
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