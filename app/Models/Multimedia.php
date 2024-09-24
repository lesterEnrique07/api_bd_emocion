<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multimedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'tipo',
        'direccion_url',
        'sesion_id'
    ];

    public function sesion(){
        return $this->belongsTo(Sesion::class);
    }
}
