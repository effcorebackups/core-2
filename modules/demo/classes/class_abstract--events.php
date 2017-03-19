<?php

namespace effectivecore\modules\demo {
          abstract class events extends \effectivecore\events {

  static function on_code_show() {
    return 'Some text from handler "\effectivecore\modules\demo\events::on_code_show".';
  }

}}