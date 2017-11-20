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
  public $attempts = 2;
  public static $glyphs;

  static function init() {
    foreach (storage::get('settings')->select_group('captcha') as $c_settings) {
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
    return md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
  }

  static function captcha_cleaning() {
  }

  function captcha_check($characters) {
    $captcha = (new instance('captcha', [
      'id' => static::id_get()
    ]))->select();
    if ($captcha &&
        $captcha->characters === $characters) {
      $captcha->delete();
      return true;
    } else {
      if ($captcha->attempts > 1) {
        $captcha->attempts--;
        $captcha->update();
      } else {
        $captcha = $this->captcha_generate();
        $captcha->update();
        $this->child_change('canvas', $captcha->canvas);
      }
    }
  }

  function captcha_generate() {
    if (!static::$glyphs) static::init();
    $characters = '';
    $canvas = new canvas_svg(5 * $this->length, 15, 5);
    $canvas->fill('#000000', 0, 0, null, null, 1);
    for ($i = 0; $i < $this->length; $i++) {
      $c_glyph = array_rand(static::$glyphs);
      $characters.= static::$glyphs[$c_glyph];
      $canvas->glyph_set($c_glyph, rand(0, 2) - 1 + ($i * 5), rand(1, 5));
    }
    $captcha = new instance('captcha', [
      'id'          => static::id_get(),
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
      'id' => static::id_get()
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