<?php

namespace Filesystem;

use \Exception;

class FilesystemException extends Exception
{

  function __construct ( $message )
  {
    parent::__construct($message);
  }
}
