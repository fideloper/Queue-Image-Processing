<?php namespace Proc\Image;

class Resize {

    protected $resource;
    protected $width;
    protected $height;

    public function loadString($string)
    {
        $this->resource = imagecreatefromstring($string);
    }

    public function loadFile($file)
    {
        $this->loadResource( file_get_contents($file) )
    }

}