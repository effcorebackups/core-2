<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\timer_factory as timer;
          use \effectivecore\token_factory as token;
          use \effectivecore\locale_factory as locale;
          use \effectivecore\console_factory as console;
          use \effectivecore\message_factory as message;
          use \effectivecore\translations_factory as translations;
          use \effectivecore\modules\user\users_factory as users;
          use \effectivecore\modules\page\pages_factory as pages;
          use \effectivecore\modules\storage\storages_factory as storages;
          class page {

  public $title = '';
  public $url = null;
  public $access = null;
  public $constants = [];
  public $content = [];

  function __construct() {
  }

  function render() {
    $template = new template('page');
    $template->set_var('title',
      token::replace(translations::get($this->title))
    );

  # check https (@todo: enable this message)
    if (false && !empty($this->https) && urls::get_current()->get_protocol() != 'https') {
      message::add_new('This page should be use HTTPS protocol!', 'warning');
    }

  # render frontend items: icons, styles, script
    $rendered_meta = [(new markup_simple('meta', ['charset' => 'utf-8']))->render()];
    $rendered_styles = [];
    $rendered_script = [];
    $used_links = [];
    foreach ($this->content as $c_block) {
      if ($c_block->type == 'link') {
        $used_links[] = $c_block->npath;
      }
    }
    foreach (storages::get('settings')->select_group('frontend') as $module_id => $c_frontend_items) {
      foreach ($c_frontend_items as $c_item) {
        if (    (isset($c_item->display->url->match) &&
            preg_match($c_item->display->url->match, urls::get_current()->path)) ||
                (isset($c_item->display->npath->match) && $c_item->display->npath->where == 'block' &&
            preg_match($c_item->display->npath->match.'m', implode(nl, $used_links)))) {

        # render meta
          if (isset($c_item->favicons)) {
            foreach ($c_item->favicons as $c_icon) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_icon->file);
              $rendered_meta[] = (new markup_simple('link', [
                'rel'   => $c_icon->rel,
                'type'  => $c_icon->type,
                'href'  => $c_url->get_full(),
                'sizes' => $c_icon->sizes
              ]))->render();
            }
          }

        # render styles
          if (isset($c_item->styles)) {
            foreach ($c_item->styles as $c_style) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_style->file);
              $rendered_styles[] = (new markup_simple('link', [
                'rel'   => 'stylesheet',
                'media' => $c_style->media,
                'href'  => $c_url->get_full()
              ]))->render();
            }
          }

        # render script
          if (isset($c_item->script)) {
            foreach ($c_item->script as $c_script) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_script->file);
              $rendered_script[] = (new markup('script', [
                'src' => $c_url->get_full()
              ]))->render();
            }
          }

        }
      }
    }

  # collect page arguments
    if (isset($this->display->url->args)) {
      foreach ($this->display->url->args as $c_name => $c_num) {
        pages::$args[$c_name] = urls::get_current()->get_args($c_num);
      }
    }

  # collect page content
    $contents = [];
    foreach ($this->content as $c_block) {
      $c_region = isset($c_block->region) ?
                        $c_block->region : 'content_1_1';
      switch ($c_block->type) {
        case 'text': $contents[$c_region][] = new text($c_block->content); break;
        case 'code': $contents[$c_region][] = call_user_func_array($c_block->handler, pages::$args); break;
        case 'link': $contents[$c_region][] = storages::get('settings')->select_by_npath($c_block->npath); break;
        default    : $contents[$c_region][] = $c_block;
      }
    }

  # render each block
    foreach ($contents as $c_region_name => $c_blocks) {
      $rendered_c_region = '';
      foreach ($c_blocks as $c_block) {
        $rendered_c_region.= method_exists($c_block, 'render') ?
                                           $c_block->render() :
                                           $c_block;
      }
      $template->set_var($c_region_name,
        $rendered_c_region
      );
    }
    timer::tap('total');
    console::add_information('Total build time', locale::format_msecond(timer::get_period('total', 0, 1)));
    console::add_information('User roles', implode(', ', users::get_current()->roles));
    console::add_information('Server load (sys_getloadavg)', locale::format_msecond(sys_getloadavg()[0]));
    console::add_information('Memory for php (bytes)', locale::format_number(memory_get_usage(true), 0, null, ' '));
    console::add_information('Current language', locale::get_settings()->lang_code);

    $template->set_var('attributes', factory::data_to_attr(['lang' => locale::get_settings()->lang_code]));
    $template->set_var('console', console::render()); # @todo: only for admins
    $template->set_var('messages', message::render_all());
    $template->set_var('meta', implode(nl, $rendered_meta));
    $template->set_var('styles', implode(nl, $rendered_styles));
    $template->set_var('script', implode(nl, $rendered_script));

    return $template->render();
  }

}}