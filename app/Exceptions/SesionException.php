<?php

namespace App\Exceptions;
use Exception;
class SesionException extends Exception{
    public function __construct($message){
        parent::__construct($message);
    }
}