<?php

namespace effectivecore {
          abstract class parser {

  static function parse_settings($data) {
    $return = new \StdClass();
    $p = [-1 => &$return];
    foreach (explode(nl, $data) as $c_line) {
      $matches = [];
    # p.s. approximately performance - 1'000'000 strings per second.
      preg_match('%(?<indent>[ ]*)'.
                  '(?<prefix>\- |)'.
                  '(?<name>[a-z0-9_]+)'.
                  '(?<delimiter>\: |)'.
                  '(?<value>.*|)%s', $c_line, $matches);
      if ($matches['name']) {
        $depth = strlen($matches['indent'].$matches['prefix']) / 2;
        $value = $matches['delimiter'] == ': ' ? $matches['value'] : new \StdClass();
      # add new item to tree
        if (is_array($p[$depth-1])) {
          $p[$depth-1][$matches['name']] = $value;
          $p[$depth] = &$p[$depth-1][$matches['name']];
        } else {
          $p[$depth-1]->{$matches['name']} = $value;
          $p[$depth] = &$p[$depth-1]->{$matches['name']};
        }
      # convert parent item to array
        if ($matches['prefix'] == '- ' && !is_array($p[$depth-1])) {
          $p[$depth-1] = (array)$p[$depth-1];
        }
      }
    }
    return $return;
  }

}}