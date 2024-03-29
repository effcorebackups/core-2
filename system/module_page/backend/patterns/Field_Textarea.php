<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_Textarea extends Field_Text {

    public $title = 'Text area';
    public $attributes = [
        'data-type' => 'textarea'];
    public $element_class = '\\effcore\\Markup';
    public $element_tag_name = 'textarea';
    public $element_attributes = [
        'name'      => 'textarea',
        'required'  => true,
        'rows'      => 5,
        'minlength' => 5,
        'maxlength' => 255];
    public $is_unix_line_endings_get = true;

    function build() {
        if (!$this->is_builded) {
            parent::build();
            $element_value = $this->attribute_select('value', 'element_attributes');
                             $this->attribute_delete('value', 'element_attributes');
            $element = $this->child_select('element');
            $element->attribute_delete('value');
            $element->child_insert(new Text_simple($element_value ?: ''), 'content');
            $this->is_builded = true;
        }
    }

    function value_get() { # @return: string | __OTHER_TYPE__ (when "value" in *.data is another type)
        $content = $this->child_select('element')->child_select('content');
        if ($this->is_unix_line_endings_get === true) return str_replace(CR.NL, NL, $content->text_select());
        if ($this->is_unix_line_endings_get !== true) return                        $content->text_select();
    }

    function value_set($value) {
        $this->value_set_initial($value);
        if (is_null   ($value)) return $this->child_select('element')->child_select('content')->text_delete();
        if (is_int    ($value)) return $this->child_select('element')->child_select('content')->text_update(Core::format_number($value));
        if (is_float  ($value)) return $this->child_select('element')->child_select('content')->text_update(Core::format_number($value, Core::FPART_MAX_LEN));
        if (is_string ($value)) return $this->child_select('element')->child_select('content')->text_update($value);
    }

}
