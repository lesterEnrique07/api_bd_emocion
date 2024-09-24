<?php

namespace App\Exceptions;
use Exception;
class MultimediaException extends Exception{
    public function __construct($message){
        parent::__construct($message);
    }
}