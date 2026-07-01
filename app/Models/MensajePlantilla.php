<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajePlantilla extends Model
{
    protected $fillable = ['nombre', 'evento', 'contenido', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
    ];
}
