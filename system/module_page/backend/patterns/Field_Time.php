<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_Time extends Field_Text {

    use Field__Shared;

    const INPUT_MIN_TIME = '00:00:00';
    const INPUT_MAX_TIME = '23:59:59';

    public $title = 'Time';
    public $attributes = [
        'data-type' => 'time'];
    public $element_attributes = [
        'type'     => 'time',
        'name'     => 'time',
        'required' => true,
        'min'      => self::INPUT_MIN_TIME,
        'max'      => self::INPUT_MAX_TIME,
        'step'     => 1];
    public $value_current_if_null = false;

    function build() {
        if (!$this->is_builded) {
            parent::build();
            $this->value_set(parent::value_get());
        }
    }

    function value_set($value) {
        $this->value_set_initial($value);
        if (is_null  ($value) && $this->value_current_if_null !== true) return parent::value_set('');
        if (is_null  ($value) && $this->value_current_if_null === true) return parent::value_set(Locale::time_utc_to_loc(Core::time_get()));
        if (is_string($value))                                          return parent::value_set($value);
    }

    ###########################
    ### static declarations ###
    ###########################

    static function on_validate($field, $form, $npath) {
        $element = $field->child_select('element');
        $name = $field->name_get();
        $type = $field->type_get();
        if ($name && $type) {
            if ($field->disabled_get()) return true;
            if ($field->readonly_get()) return true;
            $new_value = Request::value_get($name, static::current_number_generate($name), $form->source_get());
            $new_value = strlen($new_value) === 5 ? $new_value.':00' : $new_value;
            $old_value = $field->value_get_initial();
            $result = static::validate_required  ($field, $form, $element, $new_value) &&
                      static::validate_minlength ($field, $form, $element, $new_value) &&
                      static::validate_maxlength ($field, $form, $element, $new_value) &&
                      static::validate_value     ($field, $form, $element, $new_value) &&
                      static::validate_min       ($field, $form, $element, $new_value) &&
                      static::validate_max       ($field, $form, $element, $new_value) &&
                      static::validate_pattern   ($field, $form, $element, $new_value) && (!empty($field->is_validate_uniqueness) ?
                      static::validate_uniqueness($field,                  $new_value, $old_value) : true);
            $field->value_set($new_value);
            return $result;
        }
    }

    static function validate_value($field, $form, $element, &$new_value) {
        if (strlen($new_value) && !Security::validate_time($new_value)) {
            $field->error_set(
                'Value of "%%_title" field is not a valid time!', ['title' => (new Text($field->title))->render() ]
            );
        } else {
            return true;
        }
    }

}
