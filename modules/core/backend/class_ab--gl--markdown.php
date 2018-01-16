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
      $c_level = (int)floor((($c_indent - 1) / 4) + 1.25);
      $c_item = $pool->child_select_last();
      $c_type = $c_item instanceof markup ||
                $c_item instanceof markup_simple ?
                $c_item->tag_name : ($c_item instanceof text ? '__text__' : null);
      $c_type = $c_type == 'ul' ? '__list__'   : $c_type;
      $c_type = $c_type == 'ol' ? '__list__'   : $c_type;
      $c_type = $c_type == 'h1' ? '__header__' : $c_type;
      $c_type = $c_type == 'h2' ? '__header__' : $c_type;
      $c_type = $c_type == 'h3' ? '__header__' : $c_type;
      $c_type = $c_type == 'h4' ? '__header__' : $c_type;
      $c_type = $c_type == 'h5' ? '__header__' : $c_type;
      $c_type = $c_type == 'h6' ? '__header__' : $c_type;
      $c_matches = [];

    # headers
    # ─────────────────────────────────────────────────────────────────────
      $n_header = null;
      if (preg_match('%^(?<marker>[-=]+)[ ]*$%S', $c_string, $c_matches)) {
        if ($c_matches['marker'][0] == '=') $n_header = new markup('h1', [], $strings[$c_num-1]);
        if ($c_matches['marker'][0] == '-') $n_header = new markup('h2', [], $strings[$c_num-1]);
      # remove previous insertion
        if ($c_type == 'p' && $c_item->child_select_first() instanceof text) $pool->child_delete($pool->child_select_last_id());
        if ($c_type == '__header__') $pool->child_delete($pool->child_select_last_id());
        if ($c_type == 'hr')         $pool->child_delete($pool->child_select_last_id());
      }
      if (preg_match('%^(?<marker>[#]{1,6})(?<return>.*)$%S', $c_string, $c_matches)) {
        $n_header = new markup('h'.strlen($c_matches['marker']), [], $c_matches['return']);
      }
      if ($n_header) {
      # special case: list|header
        if ($c_type == '__list__') {
          if (!empty($c_item->_p[$c_level]))           {$c_item->_p[$c_level]          ->child_select_last()->child_insert($n_header); continue;}
          if (!empty($c_item->_p[count($c_item->_p)])) {$c_item->_p[count($c_item->_p)]->child_select_last()->child_insert($n_header); continue;}
        }
      # default case
        $pool->child_insert($n_header);
        continue;
      }

    # horizontal rules
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>([*][ ]{0,2}){3,}|'.
                                 '([-][ ]{0,2}){3,}|'.
                                 '([_][ ]{0,2}){3,})'.
                       '(?<noises>[ ]{0,})$%S', $c_string)) {
        $pool->child_insert(new markup_simple('hr'));
        continue;
      }

    # lists
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,})'.
                       '(?<marker>[*+-]|[0-9]+(?<dot>[.]))'.
                       '(?<noises>[ ]{1,})'.
                       '(?<return>[^ ].+)$%S', $c_string, $c_matches)) {
      # special cases: paragraph|list, blockquote|list, code|list
        if ($c_type == 'p')          {$c_item->child_insert(new text(nl.$c_string)); continue;}
        if ($c_type == 'blockquote') {$c_item->child_select('text')->text_append(nl.$c_string); continue;}
        if ($c_type == 'pre')        {$pool->child_insert(new text(htmlspecialchars($c_string))); continue;}
      # create new root list container (ol/ul) if $c_item is not a container
        if ($c_type != '__list__' && $c_indent < 4) {
          $c_item = new markup($c_matches['dot'] ? 'ol' : 'ul');
          $c_item->_p[1] = $c_item;
          $c_type = '__list__';
          $pool->child_insert($c_item);
        }
        if ($c_type == '__list__') {
        # create new list sub container (ol/ul)
          if (empty($c_item->_p[$c_level-0]) &&
             !empty($c_item->_p[$c_level-1])) {
            $c_olul = new markup($c_matches['dot'] ? 'ol' : 'ul');
                      $c_item->_p[$c_level-0] = $c_olul;
            $c_last = $c_item->_p[$c_level-1]->child_select_last();
            if ($c_last) $c_last->child_insert($c_olul);
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
      }

    # blockquotes
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>[>][ ]{0,1})'.
                       '(?<return>.+)$%S', $c_string, $c_matches)) {
      # create new blockquote container
        if ($c_type != 'blockquote') {
          $c_item = new markup('blockquote');
          $c_item->child_insert(new text(''), 'text');
          $pool->child_insert($c_item);
        }
      # insert new blockquote string
        $c_item->child_select('text')->text_append(
          nl.$c_matches['return']
        );
        continue;
      }

    # paragraphs
    # ─────────────────────────────────────────────────────────────────────
      if (trim($c_string) == '') {
        if ($c_type == '__text__' && trim($c_item->text_select()) == '') {
          $c_item->text_append(nl);
          continue;
        } else {
          $pool->child_insert(new text(nl));
          continue;
        }
      }
      if (trim($c_string) != '') {
      # special cases: blockquote|list, blockquote|paragraph
        if ($c_type == '__list__' && !empty($c_item->_p[$c_level]))           {$c_item->_p[$c_level]          ->child_select_last()->child_insert(new text(nl.$c_string)); continue;}
        if ($c_type == '__list__' && !empty($c_item->_p[count($c_item->_p)])) {$c_item->_p[count($c_item->_p)]->child_select_last()->child_insert(new text(nl.$c_string)); continue;}
        if ($c_type == 'blockquote') {$c_item->child_select('text')->text_append(nl.$c_string); continue;}
      # if previous paragraph was found - add text to paragraph
        if ($c_type == 'p') {
          $c_item->child_insert(new text(nl.$c_string));
          continue;
        }
      # when no previous paragraph was found - add new paragraph
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
        if ($c_type != 'pre') {
          $c_item = new markup('pre');
          $c_item->child_insert(new markup('code'), 'code');
          $pool->child_insert($c_item);
        }
      # insert new code string
        $c_item->child_select('code')->child_insert(
          new text(nl.htmlspecialchars($c_matches['return']))
        );
        continue;
      }

    }

  # postprocess for blockquote
    foreach ($pool->child_select_all_recursive() as $c_item) {
      if ($c_item instanceof markup &&
          $c_item->tag_name == 'blockquote') {
        $c_child = $c_item->child_select('text');
        if ($c_child) {
          $c_markup = trim($c_child->text_select());
          if ($c_markup) {
            $c_item->child_delete('text');
            foreach (static::markdown_to_markup($c_markup)->child_select_all() as $c_new_child) {
              $c_item->child_insert($c_new_child);
            }
          }
        }
      }
    }

    return $pool;
  }

}}