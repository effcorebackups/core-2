<?php

namespace effectivecore {
          class markup {

  public $type;
  public $attributes;
  public $content;

  function render() {
    $rendered = [];
  # collect content
    if (is_array($this->content)) {
      foreach ($this->content as $c_element) {
        $rendered[] = method_exists($c_element, 'render') ?
                                    $c_element->render() :
                                    $c_element;
      }
    } elseif (is_string($this->content)) {
      $rendered[] = $this->content;
    }
  # generate output
    $template = new template(count($rendered) ? 'html_element' : 'html_element_simple');
    $template->set_var('type', $this->type);
    $template->set_var('attributes', implode(' ', factory::data_to_attr($this->attributes)));
    $template->set_var('content', implode(nl, $rendered));
    return $template->render();
  }

}}