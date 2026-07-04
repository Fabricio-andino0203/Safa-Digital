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

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_extra', 'extra_id', 'categoria_id');
    }
}
