<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\timers_factory as timers;
          use \effectivecore\tokens_factory as tokens;
          use \effectivecore\console_factory as console;
          use \effectivecore\locales_factory as locales;
          use \effectivecore\messages_factory as messages;
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
      tokens::replace(translations::get($this->title))
    );

  # check https (@todo: enable this message)
    if (false && !empty($this->https) && urls::get_current()->get_protocol() != 'https') {
      messages::add_new('This page should be use HTTPS protocol!', 'warning');
    }

  # render frontend items: icons, styles, script
    $rendered_meta = [(new markup('meta', ['charset' => 'utf-8']))->render()];
    $rendered_styles = '';
    $rendered_script = '';
    $used_links = [];
    foreach ($this->content as $c_block) {
      if ($c_block->type == 'link') {
        $used_links[] = $c_block->npath;
      }
    }
    foreach (storages::get('settings')->select('frontend') as $module_id => $c_frontend_items) {
      foreach ($c_frontend_items as $c_item) {
        if (    (isset($c_item->display->url->match) &&
            preg_match($c_item->display->url->match, urls::get_current()->path)) ||
                (isset($c_item->display->npath->match) && $c_item->display->npath->where == 'block' &&
            preg_match($c_item->display->npath->match.'m', implode(nl, $used_links)))) {

        # render meta
          if (isset($c_item->favicons)) {
            foreach ($c_item->favicons as $c_icon) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_icon->file);
              $rendered_meta[] = (new markup('link', [
                'rel'   => $c_icon->rel,
                'type'  => $c_icon->type,
                'href'  => $c_url->get_full(),
                'sizes' => $c_icon->sizes
              ]))->render();
            }
            $template->set_var('meta',
              implode(nl, $rendered_meta)
            );
          }

        # render styles
          if (isset($c_item->styles)) {
            foreach ($c_item->styles as $c_style) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_style->file);
              $rendered_styles[] = (new markup('link', [
                'rel'   => 'stylesheet',
                'media' => $c_style->media,
                'href'  => $c_url->get_full()
              ]))->render();
            }
            $template->set_var('styles',
              implode(nl, $rendered_styles)
            );
          }

        # render script
          if (isset($c_item->script)) {
            foreach ($c_item->script as $c_script) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_script->file);
              $rendered_script[] = (new markup('script', [
                'src' => $c_url->get_full()
              ], ' '))->render();
              $template->set_var('script',
                implode(nl, $rendered_script)
              );
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
                        $c_block->region : 'c_1_1';
      switch ($c_block->type) {
        case 'text': $contents[$c_region][] = $c_block->content; break;
        case 'code': $contents[$c_region][] = call_user_func_array($c_block->handler, pages::$args); break;
        case 'link': $contents[$c_region][] = factory::npath_get_object($c_block->npath, storages::get('settings')->select()); break;
        default:     $contents[$c_region][] = $c_block;
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
    timers::tap('total');
    console::add_information('Total build time', locales::format_msecond(timers::get_period('total', 0, 1)));
    console::add_information('User roles', implode(', ', users::get_current()->roles));
    console::add_information('Server load (sys_getloadavg)', locales::format_msecond(sys_getloadavg()[0]));
    console::add_information('Memory for php (bytes)', locales::format_number(memory_get_usage(true), 0, null, ' '));
    console::add_information('Current language', locales::get_current()->lang_code);

    $template->set_var('html_attributes', factory::data_to_attr(['lang' => locales::get_current()->lang_code]));
    $template->set_var('console', console::render()); # @todo: only for admins
    $template->set_var('messages', messages::render());

    return $template->render();
  }

}}