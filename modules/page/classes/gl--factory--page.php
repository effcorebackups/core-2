<?php

namespace effectivecore\modules\page {
          use const \effectivecore\br;
          use \effectivecore\factory;
          use \effectivecore\url_factory as urls;
          use \effectivecore\settings_factory as settings;
          use \effectivecore\modules\user\access_factory as access;
          abstract class page_factory {

  static $data = [];
  static $args = [];

  static function render() {
    # render page
    foreach (settings::get('pages') as $c_pages) {
      foreach ($c_pages as $c_page) {
        if (isset($c_page->url->match) && preg_match($c_page->url->match, urls::get_current()->path)) {
          if (!isset($c_page->access) ||
              (isset($c_page->access) && access::check($c_page->access))) {
            return $c_page->render();
          } else {
            factory::send_header_and_exit('access_denided',
              'Access denided!'
            );
          }
        }
      }
    }
    # no matches case
    factory::send_header_and_exit('not_found',
      'Page not found!'
    );
  }

  static function add_element($element, $region = 'c_1_1') {
    static::$data[$region][] = $element;
  }

}}