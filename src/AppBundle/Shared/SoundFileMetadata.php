<?php

namespace AppBundle\Shared;

class SoundFileMetadata
{
    public $album;
    public $title;
    public $artist = 'Soundschekel';

    function __construct($album, $title) {
        $this->album = $album;
        $this->title = $title;
    }
}

?>