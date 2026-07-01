<?php

use Illuminate\Support\Facades\Cache;
use App\Models\Configuracion;

if (!function_exists('get_setting')) {
    function get_setting($llave, $default = null)
    {
        return Cache::rememberForever('configuracion_' . $llave, function () use ($llave, $default) {
            $config = Configuracion::where('llave', $llave)->first();
            return $config ? $config->valor : $default;
        });
    }
}
