<?php

namespace effectivecore\modules\demo {
          abstract class events extends \effectivecore\events {

  static function on_code_show() {
    return '[demo_code] Some code result from handler "\effectivecore\modules\demo\events::on_code_show".';
  }

}}