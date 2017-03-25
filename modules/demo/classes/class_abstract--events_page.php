<?php

namespace effectivecore\modules\demo {
          abstract class events_page extends \effectivecore\events {

  static function on_show_code() {
    return '[demo_code] Some code result from handler "\effectivecore\modules\demo\events::on_code_show".';
  }

}}