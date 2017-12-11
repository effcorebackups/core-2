<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\message_factory as message;
          use \effectivecore\modules\storage\storage_factory as storage;
          class form_field_captcha extends \effectivecore\form_field {

  public $title = 'Captcha';
  public $description = 'Write the characters from the picture.';
  public $attributes = ['class' => ['captcha' => 'captcha']];

  public $length = 6;
  public $attempts = 3;
  public $noise = 1;
  public static $glyphs;

  static function init() {
    foreach (storage::select('settings')->select_group('captcha') as $c_settings) {
      foreach ($c_settings as $c_characters) {
        foreach ($c_characters as $c_character) {
          foreach ($c_character->glyphs as $c_glyph) {
            static::$glyphs[$c_glyph] = $c_character->character;
          }
        }
      }
    }
  }

  static function id_get() {
  # duplicates of captcha by IP is not an a problem - new captcha will generate again
  # p.s. it's prevention for DOS attacks which can overflow the storage if we used
  # more unique identifier than IP address, for example: IP + user_agent (in this situation
  # client can falsify user_agent on each http request and this will create a great variety
  # of unique client id)
    return $_SERVER['REMOTE_ADDR'];
  }

  static function captcha_cleaning() {
    $storage = $s = storage::select(entity_factory::select('captcha')->get_storage_id());
    $storage->query('DELETE', 'FROM', $s->tables('captcha'), 'WHERE', $s->condition('created', factory::datetime_get('-1 hour'), '<'));
  }

  function captcha_check($characters) {
    $captcha = (new instance('captcha', [
      'ip_address' => static::id_get()
    ]))->select();
    if ($captcha) {
      if ($captcha->attempts > 0) {
        $captcha->attempts--;
        $captcha->update();
      } else {
        $captcha = $this->captcha_generate();
        $captcha->update();
        $this->child_change('canvas', $captcha->canvas);
      }
      if ($captcha->characters === $characters) {
        return true;
      }
    }
  }

  function captcha_generate() {
    if (!static::$glyphs) static::init();
    $characters = '';
    $canvas = new canvas_svg(5 * $this->length, 15, 5);
    $canvas->fill('#000000', 0, 0, null, null, $this->noise);
    for ($i = 0; $i < $this->length; $i++) {
      $c_glyph = array_rand(static::$glyphs);
      $characters.= static::$glyphs[$c_glyph];
      $canvas->glyph_set($c_glyph, rand(0, 2) - 1 + ($i * 5), rand(1, 5));
    }
    $captcha = new instance('captcha', [
      'ip_address'  => static::id_get(),
      'characters'  => $characters,
      'created'     => factory::datetime_get(),
      'attempts'    => $this->attempts,
      'canvas'      => $canvas,
      'canvas_data' => $canvas->clmask_to_hexstr()
    ]);
    return $captcha;
  }

  function captcha_load() {
    $captcha = (new instance('captcha', [
      'ip_address' => static::id_get()
    ]))->select();
    if ($captcha) {
      $captcha->canvas = new canvas_svg(5 * $this->length, 15, 5);
      $captcha->canvas->matrix_set(
        $captcha->canvas->hexstr_to_clmask(
          $captcha->canvas_data
        )
      );
      return $captcha;
    }
  }

  function build() {
    $captcha = $this->captcha_load();
    if (!$captcha) {
      $captcha = $this->captcha_generate();
      $captcha->insert();
    }
  # build form elements
    $this->child_insert($captcha->canvas, 'canvas');
    $this->child_insert(new markup_simple('input', [
      'type' => 'text',
      'name' => 'captcha',
      'size' => $this->length,
      'required' => 'required',
      'minlength' => $this->length,
      'maxlength' => $this->length
    ]), 'element');
  }

}}