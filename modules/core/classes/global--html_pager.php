<?php

namespace effectivecore {
          class html_pager extends html {

  public $name;
  public $has_error = false;
  public $pages_total;
  public $items_total;
  public $items_per_page;
  public $c_page_num;

  function __construct($attr = [], $items_total, $items_per_page, $name = 'main') {
    parent::__construct('pager', $attr);
    $c_url = urls::$current;
    $c_url_page_num = $c_url->get_args('page');
    $this->name = $name;
    $this->pages_total = $items_total ? ceil($items_total / $items_per_page) : 1;
    $this->items_total = $items_total;
    $this->items_per_page = $items_per_page;
    $this->c_page_num = isset($c_url_page_num[$name]) ? $c_url_page_num[$name] : '1';
  # check the c_page_num
    if ($this->c_page_num !== (string)(int)$this->c_page_num ||
        $this->c_page_num < 1 ||
        $this->c_page_num > $this->pages_total) {
      $this->has_error = true;
      return;
    }
  # build the pager
    if ($this->pages_total > 1) {
    # prepare the series
      $series[] = 1;
      if ($this->c_page_num - 10 > 2) {
        $series[] = '...';
      }
      for ($i = -10; $i <= 10; $i++) {
        if ($this->c_page_num + $i > 1 &&
            $this->c_page_num + $i < $this->pages_total) {
          $series[] = $this->c_page_num + $i;
        }
      }
      if ($this->c_page_num + 10 < $this->pages_total - 1) {
        $series[] = '...';
      }
      $series[] = $this->pages_total;
    # add links to the pager
      foreach ($series as $c_num) {
        $query_args = $c_url->query_args;
        $query_args['page'][$name] = $c_num;
        $c_url->query_args = $query_args;
        $this->add_element($c_num == '...' ?
          new markup('span', [], $c_num) :
          new markup('a', $this->c_page_num == $c_num ? ['class' => ['active']] : ['href' => $c_url->full], $c_num)
        );
      }
    }
  }

}}