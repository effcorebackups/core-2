<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\demo;

use effcore\Entity;
use effcore\Text_multiline;
use effcore\Text;

abstract class Events_Form_Instance_update {

    static function on_validate($event, $form, $items) {
        $entity = Entity::get($form->entity_name);
        switch ($form->clicked_button->value_get()) {
            case 'update':
                if ($entity->name === 'demo_join' && !$form->has_error()) {
                    # field 'id_data'
                    $id_data_new = (string)$items['#id_data']->value_get        ();
                    $id_data_old = (string)$items['#id_data']->value_get_initial();
                    if ($id_data_new !== $id_data_old) {
                        $result = $entity->instances_select([
                            'where' => [
                                'id_data_!f'       => 'id_data',
                                'id_data_operator' => '=',
                                'id_data_!v'       => $id_data_new],
                            'limit' => 1]);
                        if ($result) {
                            $items['#id_data']->error_set(new Text_multiline([
                                'Field "%%_title" contains a combination of values that is already in use!',
                                'Combination of values should be unique.'], ['title' => (new Text($items['#id_data']->title))->render() ]
                            ));
                        }
                    }
                }
                break;
        }
    }

}
