<?php

namespace App\Exceptions;
use Exception;
class PacienteException extends Exception{
    public function __construct($message){
        parent::__construct($message);
    }
}