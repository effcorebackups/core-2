<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\urls_factory as urls;
          use \effectivecore\timers_factory as timers;
          use \effectivecore\tokens_factory as tokens;
          use \effectivecore\console_factory as console;
          use \effectivecore\messages_factory as messages;
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\user\user_factory as users;
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

  # collect frontend
    $rendered_styles = '';
    $rendered_script = '';
    $parts = storages::get('settings')->select('frontend');
    foreach ($parts as $module_id => $c_part_group) {
      foreach ($c_part_group as $c_part) {
        if (isset($c_part->url->match) && preg_match($c_part->url->match, urls::get_current()->path)) {

        # set meta
          $meta_items = [(new markup('meta', ['charset' => 'utf-8']))->render()];
          if (isset($c_part->favicons)) {
            foreach ($c_part->favicons as $c_icon) {
              $c_url = new url('/modules/'.$module_id.'/'.$c_icon->file);
              $meta_items[] = (new markup('link', [
                'rel'   => 'icon',
                'type'  => 'image/png',
                'sizes' => $c_icon->sizes,
                'href'  => $c_url->get_full()
              ]))->render();
            }
            $template->set_var('meta',
              implode(nl, $meta_items)
            );
          }

        # collect styles
          if (isset($c_part->styles)) {
            foreach ($c_part->styles as $c_style) {
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

        # collect script
          if (isset($c_part->script)) {
            foreach ($c_part->script as $c_script) {
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
    if (isset($this->url->args)) {
      foreach ($this->url->args as $c_name => $c_num) {
        pages::$args[$c_name] = urls::get_current()->get_args($c_num);
      }
    }

  # collect page content
    $contents = [];
    foreach ($this->content as $c_content) {
      $c_region = isset($c_content->region) ? $c_content->region : 'c_1_1';
      switch ($c_content->type) {
        case 'text': $contents[$c_region][] = $c_content->content; break;
        case 'code': $contents[$c_region][] = call_user_func_array($c_content->handler, pages::$args); break;
        case 'link': $contents[$c_region][] = factory::npath_get_object($c_content->link, storages::get('settings')->select()); break;
        default:     $contents[$c_region][] = $c_content;
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
    console::add_information('Total build time', timers::get_period('total', 0, 1));
    console::add_information('User roles', implode(', ', users::get_current()->roles));
    console::add_information('Server load (sys_getloadavg)', number_format(sys_getloadavg()[0], 6));
    console::add_information('Memory for php (bytes)', number_format(memory_get_usage(true), 0, '.', ' '));

    $template->set_var('html_attributes', factory::data_to_attr(['lang' => translations::$lang_current]));
    $template->set_var('console', console::render()); # @todo: only for admins
    $template->set_var('messages', messages::render());

    return $template->render();
  }

}}