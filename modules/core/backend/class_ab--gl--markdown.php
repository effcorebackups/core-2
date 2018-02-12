<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class markdown {

  static function _node_universal_type_get($node) {
    $type = $node instanceof markup ||
            $node instanceof markup_simple ? $node->tag_name : (
            $node instanceof text ? 'text' : null);
    if ($type == 'ul') return 'list';
    if ($type == 'ol') return 'list';
    if ($type == 'h1') return 'header';
    if ($type == 'h2') return 'header';
    if ($type == 'h3') return 'header';
    if ($type == 'h4') return 'header';
    if ($type == 'h5') return 'header';
    if ($type == 'h6') return 'header';
    return $type;
  }

  static function _list_data_insert($list, $data, $level) {
    if (empty($list->_wr_name))                $list->_wr_name = 'wr_data0';
    if (is_string($data) && trim($data) == '') $list->_wr_name = 'wr_data1';
    $level = $level == -1 || $list->_wr_name == 'wr_data0' ? count($list->_p_list) : $level;
    $acceptor = empty($list->_p_list[$level]) ? null :
                      $list->_p_list[$level];
    if ($acceptor) $acceptor = $acceptor->child_select_last();
    if ($acceptor) $acceptor = $acceptor->child_select($list->_wr_name);
    if ($acceptor) {
      if ($list->_wr_name == 'wr_data0') {
        $acceptor->child_insert(
          is_string($data) ? new text(nl.$data) : $data
        );
      }
      if ($list->_wr_name == 'wr_data1') {
        if (is_string($data) && trim($data) == '') {
          $acceptor->child_insert(
            new markup('p')
          );
        }
        $p = $acceptor->child_select_last();
        if ($p instanceof markup &&
            $p->tag_name == 'p') {
          $p->child_insert(
            is_string($data) ? new text(nl.$data) : $data
          );
        }
      }
    }
  }

  static function markdown_to_markup($markdown) {
    $pool = new node();
    $strings = explode(nl, $markdown);
    foreach ($strings as $c_num => $c_string) {
      $c_string = str_replace(tb, '    ', $c_string);
      $c_indent = strspn($c_string, ' ');
      $l_level = (int)floor((($c_indent - 0) / 4) + 1) ?: 1;
      $p_level = (int)floor((($c_indent - 1) / 4) + 1) ?: 1;
      $item_last = $pool->child_select_last();
      $item_prev = $pool->child_select_prev($item_last);
      $type_last = static::_node_universal_type_get($item_last);
      $type_prev = static::_node_universal_type_get($item_prev);
      $c_matches = [];

    # headers
    # ─────────────────────────────────────────────────────────────────────
      $n_header = null;
      if (preg_match('%^(?<marker>[-=]+)[ ]*$%S', $c_string, $c_matches)) {
        if ($c_matches['marker'][0] == '=') $n_header = new markup('h1', [], $strings[$c_num-1]);
        if ($c_matches['marker'][0] == '-') $n_header = new markup('h2', [], $strings[$c_num-1]);
      # remove previous insertion
        if ($type_last == 'p' && $item_last->child_select_first() instanceof text) $pool->child_delete($pool->child_select_last_id());
        if ($type_last == 'header')   $pool->child_delete($pool->child_select_last_id());
        if ($type_last == 'hr')       $pool->child_delete($pool->child_select_last_id());
      }
      if (preg_match('%^(?<marker>[#]{1,6})(?<return>.*)$%S', $c_string, $c_matches)) {
        $n_header = new markup('h'.strlen($c_matches['marker']), [], $c_matches['return']);
      }
      if ($n_header) {
      # special case: list|header
        if ($type_last == 'list') {
          static::_list_data_insert($item_last, $n_header, -1);
          continue;
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
      # special cases: p|list, blockquote|list, code|list
        if ($type_last == 'p')          {$item_last->child_insert(new text(nl.$c_string)); continue;}
        if ($type_last == 'blockquote') {$item_last->child_select('text')->text_append(nl.$c_string); continue;}
        if ($type_last == 'pre')        {$pool->child_insert(new text(htmlspecialchars($c_string))); continue;}
      # create new list container
        if ($type_last != 'list' && $c_indent < 4) {
          $item_last = new markup($c_matches['dot'] ? 'ol' : 'ul');
          $item_last->_p_list[1] = $item_last;
          $type_last = 'list';
          $pool->child_insert($item_last);
        }
        if ($type_last == 'list') {
        # create new list sub container (ol/ul)
          if (empty($item_last->_p_list[$l_level-0]) &&
              empty($item_last->_p_list[$l_level-1]) == false) {
            $new_container = new markup($c_matches['dot'] ? 'ol' : 'ul');
                         $item_last->_p_list[$l_level-0] = $new_container;
            $parent_li = $item_last->_p_list[$l_level-1]->child_select_last();
            if ($parent_li) $parent_li->child_select('wr_container')
                                      ->child_insert($new_container);
          }
        # remove old pointers to list containers (ol/ul)
          foreach ($item_last->_p_list as $c_level => $c_pointer) {
            if ($c_level > $l_level) {
              unset($item_last->_p_list[$c_level]);
            }
          }
        # insert new list item (li)
          unset($item_last->_wr_name);
          $new_li = new markup('li');
          $new_li->child_insert(new node(), 'wr_data0');
          $new_li->child_insert(new node(), 'wr_container');
          $new_li->child_insert(new node(), 'wr_data1');
          $item_last->_p_list[$l_level]->child_insert($new_li);
          static::_list_data_insert($item_last, $c_matches['return'], -1);
          continue;
        }
      }

    # blockquotes
    # ─────────────────────────────────────────────────────────────────────
      if (preg_match('%^(?<indent>[ ]{0,3})'.
                       '(?<marker>[>][ ]{0,1})'.
                       '(?<return>.+)$%S', $c_string, $c_matches)) {
      # create new blockquote container
        if ($type_last != 'blockquote') {
          $item_last = new markup('blockquote');
          $item_last->child_insert(new text(''), 'text');
          $pool->child_insert($item_last);
        }
      # insert new blockquote string
        $item_last->child_select('text')->text_append(
          nl.$c_matches['return']
        );
        continue;
      }

    # paragraphs
    # ─────────────────────────────────────────────────────────────────────
    # special case: list|text, list|nl
      if ($type_last == 'list') {
        static::_list_data_insert($item_last, $c_string, $p_level);
        continue;
      }
      if (trim($c_string) == '') {
        if ($type_last == 'text') {$item_last->text_append(nl); continue;}
        if ($type_last != 'text') {$pool->child_insert(new text(nl)); continue;}
      } else {
      # special cases: blockquote|text, p|text
        if ($type_last == 'blockquote') {$item_last->child_select('text')->text_append(nl.$c_string); continue;}
        if ($type_last == 'p')          {$item_last->child_insert(new text(nl.$c_string)); continue;}
      # special cases: |text, header|text, hr|text
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
        if ($type_last != 'pre') {
          $item_last = new markup('pre');
          $item_last->child_insert(new markup('code'), 'code');
          $pool->child_insert($item_last);
        }
      # insert new code string
        $item_last->child_select('code')->child_insert(
          new text(nl.htmlspecialchars($c_matches['return']))
        );
        continue;
      }

    }

  # postprocess for blockquote
  # ─────────────────────────────────────────────────────────────────────

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