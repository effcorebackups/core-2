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

  public $id;
  public $length = 6;
  public $attempts = 1;
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
      'characters'  => $characters,
      'canvas'      => $canvas,
      'canvas_data' => $canvas->clmask_to_hexstr(),
      'attempts'    => $this->attempts
    ]);
    return $captcha;
  }

  function captcha_load($id) {
    $captcha = (new instance('captcha', ['id' => $id]))->select();
    $captcha->canvas = new canvas_svg(5 * $this->length, 15, 5);
    $captcha->canvas->matrix_set(
      $captcha->canvas->hexstr_to_clmask($captcha->canvas_data)
    );
    return $captcha;
  }

  function build() {
    $captcha_0 = $this->captcha_generate();
    $captcha_1 = $this->captcha_load('123');
  # build form elements
    $this->child_insert($captcha_0->canvas, 'canvas_0');
    $this->child_insert($captcha_1->canvas, 'canvas_1');
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'captcha_id',
      'value' => $this->id
    ]), 'captcha_id');
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