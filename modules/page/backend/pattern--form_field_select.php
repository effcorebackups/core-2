<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          class form_field_select extends form_field {

  public $values = [];
  public $selected = [];
  public $disabled = [];

  function build() {
    $this->child_insert(new markup('select', $this->attribute_select()), 'default');
    $this->child_select('default')->title = $this->title;
    foreach ($this->values as $c_id => $c_data) {
      if (is_object($c_data) &&
             !empty($c_data->title) &&
             !empty($c_data->values)) {
        if (!$this->group_select($c_id))
             $this->group_insert($c_id, $c_data->title);
        foreach ($c_data->values as $g_id => $g_data) {
          $this->value_insert($g_data, $g_id, [], $c_id);
        }
      } else {
        $this->value_insert($c_data, $c_id);
      }
    }
  }

  function group_select($id) {
    return $this->child_select('default')->child_select($id);
  }

  function group_insert($id, $title, $attr = []) {
    $this->child_select('default')->child_insert(
      new markup('optgroup', $attr + ['label' => $title]), $id
    );
  }

  function value_insert($title, $value, $attr = [], $grp_id = null) {
    $parent_el = $grp_id ? $this->child_select('default')->child_select($grp_id) :
                           $this->child_select('default');
    $new_option = new markup('option', $attr, ['content' => $title]);
    if (isset($this->selected[$value])) $new_option->attribute_insert('selected', 'selected');
    if (isset($this->disabled[$value])) $new_option->attribute_insert('disabled', 'disabled');
                                        $new_option->attribute_insert('value', $value != 'not_selected' ? $value : null);
    $parent_el->child_insert($new_option, $value);
  }

}}