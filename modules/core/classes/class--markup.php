<?php

namespace effectivecore {
          class markup {

  public $type;
  public $properties;
  public $content = [];

  function render() {
    $output = (new html($this->type, $this->properties, $this->content))->render();
    if (is_array($this->content)) {
      foreach ($this->content as $c_item) {
        $output.= $c_item->render();
      }
    }
    return $output;
  }

}}