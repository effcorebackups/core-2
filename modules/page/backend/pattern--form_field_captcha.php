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
  public $characters = '';
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

  function build() {
  # generate new captcha
    if (!static::$glyphs) static::init();
    $canvas_0 = new canvas_svg(5 * $this->length, 15, 5);
    $canvas_0->fill('#000000', 0, 0, null, null, 1);
    for ($i = 0; $i < $this->length; $i++) {
      $c_glyph = array_rand(static::$glyphs);
      $this->characters.= static::$glyphs[$c_glyph];
      $canvas_0->glyph_set($c_glyph, rand(0, 2) - 1 + ($i * 5), rand(1, 5));
    }
  # save captcha to storage
    $captcha_0 = new instance('captcha', [
      'characters' => $this->characters,
      'canvas'     => $canvas_0->clmask_to_hexstr(),
      'attempts'   => 1
    ]);
    $captcha_0->insert();
    message::add_new('New captcha id = '.$captcha_0->id);


  # load captcha from storage
    $captcha_1 = (new instance('captcha', [
      'id' => $captcha_0->id
    ]))->select();
    $canvas_1 = new canvas_svg(5 * $this->length, 15, 5);
    $canvas_1->matrix_set($canvas_1->hexstr_to_clmask($captcha_1->canvas));
  # build form elements
    $this->child_insert($canvas_0, 'canvas_0');
    $this->child_insert($canvas_1, 'canvas_1');
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