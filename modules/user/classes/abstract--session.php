<?php

namespace effectivecore\modules\user {
          use const \effectivecore\format_datetime;
          abstract class session {

  static function init($user_id = 0) {
 /* renew session for user with selected id */
    if ($user_id != 0) {
      session_start();
      table_session::insert([
        'id' => session_id(),
        'user_id' => $user_id,
        'created' => date(format_datetime, time())
      ]);
    }
 /* restore session for authenticated user */
    if ($user_id == 0 && isset($_REQUEST[session_name()])) {
      $db_session = table_session::select_one(['user_id'], ['id' => $_REQUEST[session_name()]]);
      if (isset($db_session['user_id'])) {
        $user_id = $db_session['user_id'];
        session_start();
      } else {
      # remove lost or substituted sid in browser
        setcookie(session_name(), '', 0, '/');
      }
    }
 /* init user */
    user::init($user_id);
  }

  static function destroy($user_id) {
    table_session::delete(['id' => session_id(), 'user_id' => $user_id]);
    setcookie(session_name(), '', 0, '/');
    session_destroy();
  }

}}