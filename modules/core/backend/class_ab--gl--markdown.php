<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class markdown {

  static function simple_markdown_to_markup($markdown) {
    $stack = new node();
    $p = [];
    $strings = explode(nl, $markdown);
    foreach ($strings as $c_num => $c_string) {
      $c_string = str_replace(tb, '    ', $c_string);
      $c_matches = [];

    # find headers
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^[=]+[ ]*$%S', $c_string)) {$stack->child_insert(new markup('h1', [], $strings[$c_num-1])); continue;}
      if (preg_match('%^[-]+[ ]*$%S', $c_string)) {$stack->child_insert(new markup('h2', [], $strings[$c_num-1])); continue;}
      if (preg_match('%^(?<marker>[#]{1,6})(?<data>.*)$%S', $c_string, $c_matches)) {
        $stack->child_insert(new markup('h'.strlen($c_matches['marker']), [], $c_matches['data']));
        continue;
      }

    # find lists
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]*)'.
                       '(?<marker>[*+-]|[0-9]+(?<dot>.))'.
                       '(?:[ ]+)'.
                       '(?<data>[^ ].+)$%S', $c_string, $c_matches)) {
        $f_level = ((strlen($c_matches['indent']) - 1) / 4) + 1.25;
        $c_level = $f_level < 2 && empty($p[1]) ? floor($f_level) : ceil($f_level); # magnetic magic
      # remove pointers to old list containers
        for ($c_i = $c_level + 1; $c_i < count($p) + 1; $c_i++) {
          unset($p[$c_i]);
        }
      # create new list container
        if (empty($p[$c_level])) {
          $p[$c_level] = new markup($c_matches['dot'] ? 'ol' : 'ul');
          if ($c_level == 1) $stack->child_insert($p[1]);
          if ($c_level >= 2) $p[$c_level-1]->child_select_last()->child_insert($p[$c_level]);
        }
      # insert new list item
        $p[$c_level]->child_insert(
          new markup('li', [], $c_matches['data'])
        );
        continue;
      }

    # find paragraphs
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^[^ ]+$%S', $c_string)) {
        $stack->child_insert(new markup('p', [], $c_string));
      }

    }
    return $stack;
  }

}}