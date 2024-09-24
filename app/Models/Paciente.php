<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'correo',
        'usuario',
        'contrasena'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'datetime:d/m/Y',
    ];

    public function sesions()
    {
        return $this->hasMany(Sesion::class);
    }
}
