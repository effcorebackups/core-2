<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class markdown {

  static function markdown_to_markup($markdown) {
    $pool = new node();
    $strings = explode(nl, $markdown);
    foreach ($strings as $c_num => $c_string) {
      $c_string = str_replace(tb, '    ', $c_string);
      $c_indent = strspn($c_string, ' ');
      $c_level = floor((($c_indent - 1) / 4) + 1.25);
      $c_item = $pool->child_select_last();
      $c_matches = [];

    # headers
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<marker>[-=]+)[ ]*$%S', $c_string, $c_matches)) {
        if ($c_item instanceof markup &&
            $c_item->tag_name == 'p'  &&
            $c_item->child_select_first() instanceof text)
        $pool->child_delete($pool->child_select_last_id());
        $pool->child_insert(new markup($c_matches['marker'] == '=' ? 'h1' : 'h2', [], $strings[$c_num-1]));
        continue;
      }
      if (preg_match('%^(?<marker>[#]{1,6})(?<return>.*)$%S', $c_string, $c_matches)) {
        $pool->child_insert(new markup('h'.strlen($c_matches['marker']), [], $c_matches['return']));
        continue;
      }

    # horizontal rules
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>([*][ ]{0,2}){3,}|'.
                                 '([-][ ]{0,2}){3,}|'.
                                 '([_][ ]{0,2}){3,})'.
                       '(?<noises>[ ]{0,})$%S', $c_string)) {
        $pool->child_insert(new markup_simple('hr', []));
        continue;
      }

    # lists
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,})'.
                       '(?<marker>[*+-]|[0-9]+(?<dot>[.]))'.
                       '(?<noises>[ ]{1,})'.
                       '(?<return>[^ ].+)$%S', $c_string, $c_matches)) {
      # special cases: paragraph near list, blockquote near list, code near list
        if ($c_item instanceof markup && $c_item->tag_name == 'p')          {$c_item->child_insert(new text(nl.$c_string));       continue;}
        if ($c_item instanceof markup && $c_item->tag_name == 'blockquote') {$c_item->child_insert(new text(nl.$c_string));       continue;}
        if ($c_item instanceof markup && $c_item->tag_name == 'pre')        {$pool->child_insert(new markup('p', [], $c_string)); continue;}
      # create new root list container (ol/ul) if $c_item is not a container
        if (!($c_item instanceof markup &&
             ($c_item->tag_name == 'ol' ||
              $c_item->tag_name == 'ul'))) {
          $c_item = new markup($c_matches['dot'] ? 'ol' : 'ul');
          $c_item->_p[1] = $c_item;
          $pool->child_insert($c_item);
        }
      # create new list sub container (ol/ul)
        if (empty($c_item->_p[$c_level-0]) &&
           !empty($c_item->_p[$c_level-1])) {
          $c_cont = new markup($c_matches['dot'] ? 'ol' : 'ul');
          $c_item->_p[$c_level-0] = $c_cont;
          $c_item->_p[$c_level-1]->child_select_last()->child_insert($c_cont);
        }
      # remove old pointers to list containers (ol/ul)
        for ($i = $c_level + 1; $i < count($c_item->_p) + 1; $i++) {
          unset($c_item->_p[$i]);
        }
      # insert new list item (li)
        $c_item->_p[$c_level]->child_insert(
          new markup('li', [], $c_matches['return'])
        );
        continue;
      }

    # blockquotes
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>[>][ ]{0,1})'.
                       '(?<return>.+)$%S', $c_string, $c_matches)) {
      # create new blockquote container
        if (!($c_item instanceof markup &&
              $c_item->tag_name == 'blockquote')) {
          $c_item = new markup('blockquote'); # @todo: add blockquote/p
          $pool->child_insert($c_item);
        }
      # insert new blockquote string
        $c_item->child_insert(
          new text($c_matches['return'])
        );
        continue;
      }

    # paragraphs
    # ─────────────────────────────────────────────────────────────────────
      if (trim($c_string) == '') {
        if ($c_item instanceof text && trim($c_item->text_get(), nl) == '') {
          $c_item->text_set($c_item->text_get().nl);
          continue;
        } else {
          $pool->child_insert(new text(nl));
          continue;
        }
      }
      if (trim($c_string) != '') {
        if ($c_item instanceof markup && $c_item->tag_name == 'p')          {$c_item->child_insert(new text(nl.$c_string)); continue;}
        if ($c_item instanceof markup && $c_item->tag_name == 'blockquote') {$c_item->child_insert(new text(nl.$c_string)); continue;}
        if ($c_item instanceof markup && (
            $c_item->tag_name == 'ol' ||
            $c_item->tag_name == 'ul')) {
          if (!empty($c_item->_p[$c_level]))           {$c_item->_p[$c_level]          ->child_select_last()->child_insert(new text(nl.$c_string)); continue;}
          if (!empty($c_item->_p[count($c_item->_p)])) {$c_item->_p[count($c_item->_p)]->child_select_last()->child_insert(new text(nl.$c_string)); continue;}
        }
        if ($c_indent < 4) {
          $pool->child_insert(new markup('p', [], $c_string));
          continue;
        }
      }

    # code (last prioruty)
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{4})'.
                       '(?<noises>[ ]{0,})'.
                       '(?<return>[^ ].*)$%S', $c_string, $c_matches)) {
      # create new code container
        if (!($c_item instanceof markup &&
              $c_item->tag_name == 'pre')) {
          $c_item = new markup('pre');
          $c_item->child_insert(new markup('code'), 'code');
          $pool->child_insert($c_item);
        }
      # insert new code string
        $c_item->child_select('code')->child_insert(
          new text($c_matches['return'])
        );
        continue;
      }

    }

    return $pool;
  }

}}