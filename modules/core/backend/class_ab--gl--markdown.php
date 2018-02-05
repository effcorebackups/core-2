<?php

  ##################################################################
  ### Copyright © 2017—2018 Maxim Rysevets. All rights reserved. ###
  ##################################################################

namespace effectivecore {
          abstract class markdown {

  static function _node_get_universal_type($node) {
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

  static function _list_insert_data($list, $data, $level) {
    $container = !empty($list->_p[$level]) ?
                        $list->_p[$level] : (
                 !empty($list->_p[count($list->_p)]) ? 
                        $list->_p[count($list->_p)] : null);
    if ($container) {
      $last_li = $container->child_select_last();
      $wr_data0     = $last_li->child_select('wr_data0');
      $wr_container = $last_li->child_select('wr_container');
      $wr_data1     = $last_li->child_select('wr_data1');
      if ($wr_data0->child_count() == 0)
           $wr_data0->child_insert(is_string($data) ? new text(nl.$data) : $data);
      else $wr_data1->child_insert(is_string($data) ? new text(nl.$data) : $data);
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
      $type_last = static::_node_get_universal_type($item_last);
      $type_prev = static::_node_get_universal_type($item_prev);
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
          static::_list_insert_data($item_last, $n_header, $l_level);
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
      # cases: p|list, blockquote|list, code|list
        if ($type_last == 'p')          {$item_last->child_insert(new text(nl.$c_string)); continue;}
        if ($type_last == 'blockquote') {$item_last->child_select('text')->text_append(nl.$c_string); continue;}
        if ($type_last == 'pre')        {$pool->child_insert(new text(htmlspecialchars($c_string))); continue;}
      # create new list container
        if ($type_last != 'list' && $c_indent < 4) {
          $item_last = new markup($c_matches['dot'] ? 'ol' : 'ul');
          $item_last->_p[1] = $item_last;
          $type_last = 'list';
          $pool->child_insert($item_last);
        }
        if ($type_last == 'list') {
        # create new list sub container (ol/ul)
          if (empty($item_last->_p[$l_level-0]) &&
              empty($item_last->_p[$l_level-1]) == false) {
            $new_container = new markup($c_matches['dot'] ? 'ol' : 'ul');
                         $item_last->_p[$l_level-0] = $new_container;
            $parent_li = $item_last->_p[$l_level-1]->child_select_last();
            if ($parent_li) $parent_li->child_select('wr_container')
                                      ->child_insert($new_container);
          }
        # remove old pointers to list containers (ol/ul)
          for ($i = $l_level + 1; $i < count($item_last->_p) + 1; $i++) {
            unset($item_last->_p[$i]);
          }
        # insert new list item (li)
          $new_li = new markup('li');
          $new_li->child_insert(new node(), 'wr_data0');
          $new_li->child_insert(new node(), 'wr_container');
          $new_li->child_insert(new node(), 'wr_data1');
          $item_last->_p[$l_level]->child_insert($new_li);
          static::_list_insert_data($item_last, $c_matches['return'], $l_level);
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
      if (trim($c_string) == '') {
        if ($type_last == 'text') {$item_last->text_append(nl); continue;}
        if ($type_last != 'text') {$pool->child_insert(new text(nl)); continue;}
      }
      if (trim($c_string) != '') {
      # cases: list||p, list|p
        if ($type_prev == 'list' &&
            $type_last == 'text' && trim($item_last->text_select()) == '') {
          static::_list_insert_data($item_prev, $c_string, $p_level);
          continue;
        }
        if ($type_last == 'list') {
          static::_list_insert_data($item_last, $c_string, $l_level);
          continue;
        }
      # cases: blockquote|p, p|p
        if ($type_last == 'blockquote') {$item_last->child_select('text')->text_append(nl.$c_string); continue;}
        if ($type_last == 'p')          {$item_last->child_insert(new text(nl.$c_string)); continue;}
      # cases: |p, header|p, hr|p
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