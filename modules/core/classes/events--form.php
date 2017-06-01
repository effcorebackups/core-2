<?php

namespace effectivecore {
          abstract class events_form extends events {

  static function on_render   ($page_args, $form_args, $post_args) {}
  static function on_validate ($page_args, $form_args, $post_args) {}
  static function on_submit   ($page_args, $form_args, $post_args) {}

}}