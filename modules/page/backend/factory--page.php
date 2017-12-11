<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\page {
          use \effectivecore\factory;
          use \effectivecore\url_factory as url;
          use \effectivecore\modules\user\access_factory as access;
          use \effectivecore\modules\storage\storage_factory as storage;
          abstract class page_factory {

  static $data = [];
  static $args = [];

  static function find_and_render() {
  # render page
    foreach (storage::select('settings')->select_group('pages') as $c_pages) {
      foreach ($c_pages as $c_page) {
        if (   isset($c_page->display->url->match) &&
          preg_match($c_page->display->url->match, url::select_current()->path)) {
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

}}