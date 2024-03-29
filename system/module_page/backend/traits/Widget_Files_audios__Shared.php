<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

trait Widget_Files_audios__Shared {

    public $cover_is_allowed = true;
    public $cover_thumbnails = [
        'small'  => 'small',
        'middle' => 'middle'];
    public $cover_max_file_size = '1M';
    public $cover_types_allowed = [
        'png'  => 'png',
        'gif'  => 'gif',
        'jpg'  => 'jpg',
        'jpeg' => 'jpeg'];
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    public $audio_player_on_manage_is_visible = true;
    public $audio_player_on_manage_settings = [
        'data-player-name' => 'default',
        'autoplay'         => null,
        'controls'         => true,
        'crossorigin'      => null,
        'loop'             => null,
        'muted'            => null,
        'preload'          => 'metadata'];
    public $audio_player_default_settings = [
        'data-player-name' => 'default',
        'autoplay'         => null,
        'controls'         => true,
        'crossorigin'      => null,
        'loop'             => null,
        'muted'            => null,
        'preload'          => 'metadata'
    ];

}
