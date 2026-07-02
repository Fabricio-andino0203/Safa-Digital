<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoExtra extends Model
{
    protected $table = 'producto_extras';

    protected $fillable = [
        'producto_id',
        'nombre',
        'costo',
        'precio'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
