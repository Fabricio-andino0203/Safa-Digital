@extends('layouts.app')

@section('header_title', 'Galería de Diseños')

@section('content')
<div class="space-y-8">
    
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Archivos y Artes</h2>
            <p class="text-neutral-500 text-sm mt-1">Biblioteca visual de diseños asociados a pedidos.</p>
        </div>
        <button class="px-5 py-2.5 bg-white border border-neutral-200 text-neutral-900 text-sm font-medium rounded-xl hover:bg-neutral-50 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
            Subir Diseño Libre
        </button>
    </div>

    <!-- Grid Estilo Galería (4 Columnas) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        
        @forelse($disenos ?? [] as $diseno)
        <!-- Tarjeta de Diseño -->
        <div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow cursor-pointer group">
            
            <!-- Simulación de Miniatura / Thumbnail -->
            <div class="h-40 bg-neutral-50 border-b border-neutral-100 flex items-center justify-center relative">
                <!-- Icono Placeholder de Imagen/Archivo -->
                <svg class="w-12 h-12 text-neutral-300 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                
                <!-- Tag superpuesto de estado del pedido asociado -->
                @if($diseno->pedido)
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-white/90 text-neutral-900 shadow-sm backdrop-blur">
                            {{ $diseno->pedido->estado }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Metadatos de la Galería -->
            <div class="p-4 flex flex-col gap-1">
                <h4 class="text-sm font-semibold text-neutral-900 truncate" title="Archivo_Final_Logo.png">
                    {{ basename($diseno->url_archivo) ?? 'Diseño sin nombre' }}
                </h4>
                <p class="text-xs text-neutral-500">
                    Cliente: <span class="font-medium text-neutral-700">{{ $diseno->cliente->nombre ?? 'Desconocido' }}</span>
                </p>
                @if($diseno->notas)
                    <p class="text-xs text-neutral-400 mt-2 line-clamp-2 leading-relaxed">
                        {{ $diseno->notas }}
                    </p>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 flex flex-col items-center justify-center border-2 border-dashed border-neutral-200 rounded-2xl bg-white">
            <svg class="w-12 h-12 text-neutral-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-neutral-500 text-sm font-medium">Aún no hay diseños en la galería.</p>
        </div>
        @endforelse

    </div>
</div>
@endsection
