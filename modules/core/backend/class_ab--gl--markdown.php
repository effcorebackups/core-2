<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class markdown {

  static function simple_markdown_to_markup($markdown) {
    $stack = new node();
    $p_lists = [];
    $p_quote = null;
    $strings = explode(nl, $markdown);
    foreach ($strings as $c_num => $c_string) {
      $c_string = str_replace(tb, '    ', $c_string);
      $c_matches = [];

    # find headers
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^[=]+[ ]*$%S', $c_string)) {$stack->child_insert(new markup('h1', [], $strings[$c_num-1])); continue;}
      if (preg_match('%^[-]+[ ]*$%S', $c_string)) {$stack->child_insert(new markup('h2', [], $strings[$c_num-1])); continue;}
      if (preg_match('%^(?<marker>[#]{1,6})(?<return>.*)$%S', $c_string, $c_matches)) {
        $stack->child_insert(new markup('h'.strlen($c_matches['marker']), [], $c_matches['return']));
        continue;
      }

    # find horizontal rules
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>([*][ ]{0,2}){3,}|'.
                                 '([-][ ]{0,2}){3,}|'.
                                 '([_][ ]{0,2}){3,})'.
                       '(?<noises>[ ]{0,})$%S', $c_string)) {
        $stack->child_insert(new markup_simple('hr', []));
        $p_lists = [];
        continue;
      }

    # find blockquotes
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>[>][ ]{0,1})'.
                       '(?<return>.+)$%S', $c_string, $c_matches)) {
      # create new blockquote container
        if (empty($p_quote)) {
          $p_quote = new markup('blockquote');
          $stack->child_insert($p_quote);
        }
      # insert new blockquote string
        $p_quote->child_insert(
          new text($c_matches['return'])
        );
        continue;
      }

    # find lists
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,})'.
                       '(?<marker>[*+-]|[0-9]+(?<dot>.))'.
                       '(?<noises>[ ]{1,})'.
                       '(?<return>[^ ].+)$%S', $c_string, $c_matches)) {
        $c_level = floor(((strlen($c_matches['indent']) - 1) / 4) + 1.25);
      # remove pointers to old list containers
        for ($c_i = $c_level + 1; $c_i < count($p_lists) + 1; $c_i++) {
          unset($p_lists[$c_i]);
        }
      # create new list container
        if (empty($p_lists[$c_level])) {
          $p_lists[$c_level] = new markup($c_matches['dot'] ? 'ol' : 'ul');
          if ($c_level == 1) $stack->child_insert($p_lists[1]);
          if ($c_level >= 2) $p_lists[$c_level-1]->child_select_last()->child_insert($p_lists[$c_level]);
        }
      # insert new list item
        $p_lists[$c_level]->child_insert(
          new markup('li', [], $c_matches['return'])
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