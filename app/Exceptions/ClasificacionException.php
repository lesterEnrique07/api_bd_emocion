<?php

namespace App\Exceptions;
use Exception;
class ClasificacionException extends Exception{
    public function __construct($message){
        parent::__construct($message);
    }
}