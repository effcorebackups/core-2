<?php

namespace effectivecore {
          class url {

  # valid urls:
  # ─────────────────────────────────────────────────────────────────────
  # $urls[] =                        '/';
  # $urls[] =                        '/?key=value';
  # $urls[] =                        '/#anchor';
  # $urls[] =                        '/?key=value#anchor';
  # $urls[] =                        '/dir/subdir/page';
  # $urls[] =                        '/dir/subdir/page?key=value';
  # $urls[] =                        '/dir/subdir/page#anchor';
  # $urls[] =                        '/dir/subdir/page?key=value#anchor';
  # $urls[] =        'subdomain.domain';
  # $urls[] =        'subdomain.domain/?key=value';
  # $urls[] =        'subdomain.domain/#anchor';
  # $urls[] =        'subdomain.domain/?key=value#anchor';
  # $urls[] =        'subdomain.domain/dir/subdir/page';
  # $urls[] =        'subdomain.domain/dir/subdir/page?key=value';
  # $urls[] =        'subdomain.domain/dir/subdir/page#anchor';
  # $urls[] =        'subdomain.domain/dir/subdir/page?key=value#anchor';
  # $urls[] = 'http://subdomain.domain';
  # $urls[] = 'http://subdomain.domain/?key=value';
  # $urls[] = 'http://subdomain.domain/#anchor';
  # $urls[] = 'http://subdomain.domain/?key=value#anchor';
  # $urls[] = 'http://subdomain.domain/dir/subdir/page';
  # $urls[] = 'http://subdomain.domain/dir/subdir/page?key=value';
  # $urls[] = 'http://subdomain.domain/dir/subdir/page#anchor';
  # $urls[] = 'http://subdomain.domain/dir/subdir/page?key=value#anchor';
  # ─────────────────────────────────────────────────────────────────────

  # wrong urls:
  # ─────────────────────────────────────────────────────────────────────
  # 1. 'http://subdomain.domain/' - should be redirected to 'http://subdomain.domain'
  # 2. 'subdomain.domain/'        - should be redirected to 'http://subdomain.domain'
  # 3. '/subdomain.domain'        - this domain described like a directory (first char is the slash)
  # 4. 'dir/subdir/page'          - this directory described like a domain (first char is not the slash)
  # ─────────────────────────────────────────────────────────────────────

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. in the next url "http://login:pass@subdomain.domain:port/dir/subdir/page?key=value#anchor"
  #    the login, password and port attributes after parsing will be in the $domain property
  # ─────────────────────────────────────────────────────────────────────

  public $protocol;
  public $domain;
  public $path;
  public $query;
  public $anchor;

  function __construct($url) {
    $matches = [];
    preg_match('%(?:(?<protocol>[a-z]+)://|)'.
                   '(?<domain>[^/]*)'.
                   '(?<path>[^\?\#]*)'.
              '(?:\?(?<query>[^\#]*)|)'.
              '(?:\#(?<anchor>.*)|)%S', filter_var($url, FILTER_SANITIZE_URL), $matches);
    $this->protocol = !empty($matches['protocol']) ? $matches['protocol'] : (!empty($matches['domain']) ? 'http' : ( /* case for local ulr */ !empty($_SERVER['HTTPS']) ? 'https' : 'http'));
    $this->domain   = !empty($matches['domain'])   ? $matches['domain']   :                                        ( /* case for local ulr */ $_SERVER['HTTP_HOST']);
    $this->path     = !empty($matches['path'])     ? $matches['path']     : '/';
    $this->query    = !empty($matches['query'])    ? $matches['query']    : '';
    $this->anchor   = !empty($matches['anchor'])   ? $matches['anchor']   : '';
  }

  function get_full() {
    return $this->protocol.'://'.$this->domain.
          ($this->path == '/' && !$this->query && !$this->anchor ? '' : $this->path).
          ($this->query  ? '?'.$this->query  : '').
          ($this->anchor ? '#'.$this->anchor : '');
  }

  function get_extension() {
    return ltrim(strtolower(strrchr($this->path, '.')), '.');
  }

  function get_args($arg_id, $scope = 'path') {
    switch ($scope) {
      case 'path':
        $args = explode('/', $this->path);
        return isset($args[$arg_id]) ? $args[$arg_id] : null;
      case 'query':
        $args = [];
        parse_str($this->query, $args);
        return isset($args[$arg_id]) ? $args[$arg_id] : null;
    }
  }

}}