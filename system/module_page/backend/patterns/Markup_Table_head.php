<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Markup_Table_head extends Markup {

    public $tag_name = 'thead';

    function __construct($attributes = [], $children = [], $weight = +0) {
        parent::__construct(null, $attributes, $children, $weight);
    }

    function child_insert($child, $id = null) {
        if ($child instanceof Markup_Table_head_row) return parent::child_insert(                              $child               , $id);
        if ($child instanceof Instance             ) return parent::child_insert(new Markup_Table_head_row([], $child->values_get()), $id);
        if (is_array($child)                       ) return parent::child_insert(new Markup_Table_head_row([], $child)              , $id);
    }

}
