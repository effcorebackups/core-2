<?php

namespace effectivecore\modules\page {
          use \effectivecore\factory;
          use \effectivecore\settings;
          use \effectivecore\message;
          use \effectivecore\url;
          use \effectivecore\html;
          use \effectivecore\timer;
          use \effectivecore\token;
          use \effectivecore\template;
          use \effectivecore\console;
          use \effectivecore\modules\user\user;
          use \effectivecore\modules\user\access;
          abstract class page {

  static $template;
  static $data = [];

  static function init() {
    timer::tap('load_time');
  # create call stack and call each page
    $matches = 0;
    $denided = false;
    $call_stack = [];
    foreach (settings::$data['pages'] as $module_id => $c_pages) {
      foreach ($c_pages as $c_page) {
        if (isset($c_page->url->match) && preg_match($c_page->url->match, url::$current->path)) {
          if (!isset($c_page->access) ||
              (isset($c_page->access) && access::check($c_page->access))) {
            if ($c_page->url->match != '%.*%') $matches++;
            $c_page->module_id = $module_id;
            $call_stack[] = $c_page;
          } else {
            $denided = true;
          }
        }
      }
    }
    foreach ($call_stack as $c_page) {
    # show title
      if (isset($c_page->title)) {
        static::add_element(new html('h1', [], stripslashes(token::replace($c_page->title))), 'header');
      }
    # collect styles
      if (isset($c_page->styles)) {
        foreach ($c_page->styles as $c_style) {
          $c_style_url = new url('/modules/'.$c_page->module_id.'/'.$c_style->file);
          static::add_element(new html('style', [], '@import url("'.$c_style_url->full().'");'), 'css');
        }
      }
    # collect arguments
      $c_args = [];
      if (isset($c_page->url->args)) {
        foreach ($c_page->url->args as $c_arg_name => $c_arg_num) {
          $c_args[$c_arg_name] = url::$current->args($c_arg_num);
        }
      }
    # collect content
      if (isset($c_page->content)) {
        foreach ($c_page->content as $c_content) {
          $c_region = isset($c_content->region) ? $c_content->region : 'content';
          switch ($c_content->type) {
            case 'code' : static::add_element(call_user_func_array($c_content->handler, $c_args), $c_region); break;
            default     : static::add_element($c_content->value, $c_region);
          }
        }
      }
    }
  # special cases
    if      ($denided == true) factory::send_header_and_exit('access_denided', 'Access denided!');
    else if ($matches == 0)    factory::send_header_and_exit('not_found', 'Page not found!');
  # add messages
    message::show_after_redirect();
  # stop timer
    timer::tap('load_time');
  # set some log info
    console::set_log('Generation time', timer::get_period('load_time', 0, 1).' sec.');
    console::set_log('User roles', implode(', ', user::$current->roles));
  # @todo: show console only for admins
    static::add_element(console::render(), 'console');
  # show page
    print static::render();
  }



  static function set_template($template_name) {
    static::$template = new template($template_name);
  }

  static function add_element($element, $region = 'content') {
    static::$data[$region][] = $element;
  }



  static function render() {
    $template = new template(static::$template ?
                             static::$template : 'page');
    foreach (static::$data as $region_name => $c_blocks) {
      $output = '';
      foreach ($c_blocks as $c_block) $output.= method_exists($c_block, 'render') ? $c_block->render() : $c_block;
      $template->set_var($region_name, $output);
    }
    return $template->render();
  }

}}