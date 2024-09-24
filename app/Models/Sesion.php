<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    use HasFactory;
    protected $fillable = [
        'fecha',
        'paciente_id',
        'clasificacion_id'
    ];

    public function paciente(){
        return $this->belongsTo(Paciente::class);
    }

    public function clasificacion(){
        return $this->belongsTo(Clasificacion::class);
    }

    public function multimedia()
    {
        return $this->hasMany(Multimedia::class);
    }
}
