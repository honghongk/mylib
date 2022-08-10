<?php


namespace Filesystem;
use Filesystem\File;

class Json extends File
{
    function write ( $data )
    {
        return parent::write(json_encode($data));
    }


    function parse()
    {
        return json_decode ( file_get_contents ( $this->realpath ) , TRUE ) ;
    }

}