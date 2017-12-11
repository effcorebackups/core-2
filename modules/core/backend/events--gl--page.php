<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\translation as translation;
          abstract class events_page {

  static function on_show_block_logo($page) {
    return new markup('x-block', ['id' => 'logo'],
           new markup('a',       ['id' => 'home', 'href'  => '/', 'title' => translation::get('to home')]));
  }

  static function on_show_block_title($page) {
    return new markup('h1', ['id' => 'title'],
      token::replace(translation::get($page->title))
    );
  }

}}