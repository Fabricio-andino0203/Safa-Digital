<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PedidoArchivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'ruta',
        'nombre_original',
        'tipo',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        $defaultDisk = config('filesystems.default');
        if ($defaultDisk === 's3') {
            try {
                return Storage::disk('s3')->temporaryUrl($this->ruta, now()->addMinutes(60));
            } catch (\Exception $e) {
                return Storage::disk($defaultDisk)->url($this->ruta);
            }
        }
        return asset('storage/' . $this->ruta);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}

