<?php

namespace effectivecore {
          use \effectivecore\markup;
          abstract class events_form extends events {

  static function on_init($page_args, $form_args, $post_args) {}
  static function on_submit($page_args, $form_args, $post_args) {}

  static function on_validate($form, $elements) {
    foreach ($elements as $c_id => $c_element) {
      if ($c_element instanceof markup) {
        $c_name = isset($c_element->attributes->name) ? $c_element->attributes->name : '';
        $c_post = isset($_POST[$c_name]) ? $_POST[$c_name] : ''; # @todo: may be add a filter
        if ($c_name) {
          switch ($c_element->tag_name) {

            case 'textarea':
              $content = $c_element->get_child('content');
              $content->text = $c_post;
            # ...
              break;

            case 'input':
              $c_type = isset($c_element->attributes->type) ?
                              $c_element->attributes->type : '';
              if ($c_type &&
                  $c_type != 'file'   &&
                  $c_type != 'hidden' &&
                  $c_type != 'submit' &&
                  $c_type != 'button' &&
                  $c_type != 'reset') {

              # set post value to element
                $c_element->attributes->value = $c_post;

              # check required fields
                if ($c_post == '' && isset($c_element->attributes->required)) {
                  $form->add_error($c_id,
                    $c_element->title.' field can not be blank!'
                  );
                }

              # check min length
                if ($c_post != '' && isset($c_element->attributes->minlength) &&
                         strlen($c_post) < $c_element->attributes->minlength) {
                  $form->add_error($c_id,
                    $c_element->title.' field contain too few symbols! Minimum '.$c_element->attributes->minlength.' symbols.'
                  );
                }

              # check max length
                if ($c_post != '' && isset($c_element->attributes->maxlength) &&
                         strlen($c_post) > $c_element->attributes->maxlength) {
                  $form->add_error($c_id,
                    $c_element->title.' field contain too much symbols! Maximum '.$c_element->attributes->maxlength.' symbols.'
                  );
                }

              # check email field
                if ($c_type == 'email' &&
                    $c_post && !filter_var($c_post, FILTER_VALIDATE_EMAIL)) {
                  $form->add_error($c_id,
                    $c_element->title.' field contains an invalid email address!'
                  );
                }
              }
              break;
          }
        }
      }
    }
  }

}}