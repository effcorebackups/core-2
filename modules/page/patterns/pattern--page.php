<?php

namespace effectivecore {
          use \effectivecore\url_factory as urls;
          use \effectivecore\timer_factory as timers;
          use \effectivecore\token_factory as tokens;
          use \effectivecore\console_factory as console;
          use \effectivecore\message_factory as messages;
          use \effectivecore\translate_factory as translations;
          use \effectivecore\modules\user\user_factory as users;
          use \effectivecore\modules\page\page_factory as pages;
          use \effectivecore\modules\storage\storage_factory as storages;
          class page {

  public $title = '';
  public $url;
  public $access;
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

  # collect misc
    $rendered_styles = '';
    $rendered_script = '';
    $miscs = storages::get('settings')->select('misc');
    foreach ($miscs as $module_id => $c_misc_group) {
      foreach ($c_misc_group as $c_misc) {
        if (isset($c_misc->url->match) && preg_match($c_misc->url->match, urls::get_current()->path)) {

        # set meta
          $meta_items = [(new markup('meta', ['charset' => 'utf-8']))->render()];
          if (isset($c_misc->favicons)) {
            foreach ($c_misc->favicons as $c_icon) {
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
          if (isset($c_misc->styles)) {
            foreach ($c_misc->styles as $c_style) {
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
          if (isset($c_misc->script)) {
            foreach ($c_misc->script as $c_script) {
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

    $template->set_var('console', # @todo: only for admins
      console::render()
    );
    $template->set_var('messages',
      messages::render()
    );

    return $template->render();
  }

}}