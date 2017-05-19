<?php

namespace effectivecore\modules\page {
          use const \effectivecore\br;
          use \effectivecore\url;
          use \effectivecore\markup;
          use \effectivecore\template;
          use \effectivecore\factory;
          use \effectivecore\urls_factory as urls;
          use \effectivecore\settings_factory as settings;
          use \effectivecore\timer_factory as timer;
          use \effectivecore\token_factory as token;
          use \effectivecore\translate_factory as translate;
          use \effectivecore\console_factory as console;
          use \effectivecore\modules\user\user_factory as user;
          use \effectivecore\modules\user\access_factory as access;
          abstract class page_factory {

  static $args = [];
  static $data = [];

  static function init() {
  # create call stack and call each page
    $matches = 0;
    $denided = false;
    $call_stack = [];
    foreach (settings::get('pages') as $module_id => $c_pages) {
      foreach ($c_pages as $c_page) {
        if (isset($c_page->url->match) && preg_match($c_page->url->match, urls::get_current()->path)) {
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
        static::add_element(stripslashes(token::replace(translate::get($c_page->title))), 'title');
      }
    # collect styles
      if (isset($c_page->styles)) {
        foreach ($c_page->styles as $c_style) {
          $c_style_url = new url('/modules/'.$c_page->module_id.'/'.$c_style->file);
          static::add_element(new markup('link', [
            'rel'   => 'stylesheet',
            'media' => $c_style->media,
            'href'  => $c_style_url->get_full()]), 'styles');
        }
      }
    # collect scripts
      if (isset($c_page->scripts)) {
        foreach ($c_page->scripts as $c_script) {
          $c_script_url = new url('/modules/'.$c_page->module_id.'/'.$c_script->file);
          static::add_element(new markup('script', ['src' => $c_script_url->get_full()], ' '), 'script');
        }
      }
    # collect arguments
      if (isset($c_page->url->args)) {
        foreach ($c_page->url->args as $c_arg_name => $c_arg_num) {
          static::$args[$c_arg_name] = urls::get_current()->get_args($c_arg_num);
        }
      }
    # collect page content from settings
      if (isset($c_page->content)) {
        foreach ($c_page->content as $c_content) {
          $c_region = isset($c_content->region) ? $c_content->region : 'c_1_1';
          switch ($c_content->type) {
            case 'text': static::add_element($c_content->content, $c_region); break;
            case 'code': static::add_element(call_user_func_array($c_content->handler, static::$args), $c_region); break;
            case 'file': static::add_element('[file] is under construction', $c_region); break; # @todo: create functionality
            case 'link': static::add_element(factory::npath_get_object($c_content->link, settings::get()), $c_region); break;
            default: static::add_element($c_content, $c_region);
          }
        }
      }
    }
  # special cases
    if      ($denided == true) factory::send_header_and_exit('access_denided', 'Access denided!');
    else if ($matches == 0)    factory::send_header_and_exit('not_found', 'Page not found!');
  # move messages to last position
    $messages = static::$data['messages'];
    unset(static::$data['messages']);
    static::$data['messages'] = $messages;
  # render page
    $template = new template('page');
    foreach (static::$data as $c_region_name => &$c_blocks) { # use '&' for dynamic static::$data
      $c_region_data = [];
      foreach ($c_blocks as $c_block) {
        $c_region_data[] = method_exists($c_block, 'render') ?
                                         $c_block->render() :
                                         $c_block;
      }
      $template->set_var($c_region_name,
        implode($c_region_name == 'c_1_1' ? br : '', $c_region_data)
      );
    }
  # render page
    timer::tap('total');
    console::add_log('System', 'Total build time', '-', timer::get_period('total', 0, 1));
    console::add_log('System', 'User roles', implode(', ', user::get_current()->roles), '-');
    $template->set_var('console', console::render()); # @todo: show console only for admins
    print $template->render();
  }

  static function add_element($element, $region = 'c_1_1') {
    static::$data[$region][] = $element;
  }

}}