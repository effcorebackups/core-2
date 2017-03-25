<?php

namespace effectivecore {
          class markup {

  public $type;
  public $properties;
  public $content;

  function render() {
    $r_content = [];
  # collect content
    if (is_array($this->content)) {
      foreach ($this->content as $c_element) {
        $r_content[] = method_exists($c_element, 'render') ?
                                     $c_element->render() :
                                     $c_element;
      }
    } elseif (is_string($this->content)) {
      $r_content[] = $this->content;
    }
  # generate output
    $template = new template(count($r_content) ? 'html_element' : 'html_element_simple');
    $template->set_var('type', $this->type);
    $template->set_var('attributes', implode(' ', factory::data_to_attr($this->properties)));
    $template->set_var('content', implode(nl, $r_content));
    return $template->render();
  }

}}