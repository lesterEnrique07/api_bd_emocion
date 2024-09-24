<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clasificacion extends Model
{
    use HasFactory;
    protected $fillable = [
        'emocion_audio',
        'emocion_foto',
        'emocion_audio_foto'
    ];

    public function sesions()
    {
        return $this->hasMany(Sesion::class);
    }
}
