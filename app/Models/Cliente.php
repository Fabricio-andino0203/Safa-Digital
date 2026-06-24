<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'total_gastado'
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function disenos(): HasMany
    {
        return $this->hasMany(Diseno::class);
    }
}
