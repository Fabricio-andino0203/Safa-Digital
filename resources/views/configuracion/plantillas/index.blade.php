@extends('layouts.app')

@section('header_title', 'Configuración de Mensajes')

@section('content')
<div class="h-full flex flex-col space-y-6 overflow-y-auto">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Plantillas de Notificación</h2>
            <p class="text-neutral-500 text-sm mt-1">Configura los mensajes dinámicos para WhatsApp.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-100 flex justify-between items-center bg-[#FAFAFA]">
            <h3 class="font-bold text-neutral-800">Agregar Nueva Plantilla</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('plantillas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-1">Nombre Descriptivo</label>
                        <input type="text" name="nombre" placeholder="Ej. Pedido Creado" required class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-sm focus:border-neutral-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-1">Evento Interno (Identificador)</label>
                        <input type="text" name="evento" placeholder="Ej. pedido_creado" required class="w-full rounded-xl border border-neutral-200 px-4 py-2.5 text-sm focus:border-neutral-900 focus:outline-none font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1">Contenido del Mensaje</label>
                    <p class="text-xs text-neutral-500 mb-2">Puedes usar variables: {cliente}, {telefono}, {orden}, {fecha_entrega}, {total}, {abonado}, {saldo}, {empresa}, [rastreo]</p>
                    <textarea name="contenido" rows="4" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none" placeholder="Hola {cliente}, tu pedido {orden} está en proceso..."></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="activa" value="0">
                    <input type="checkbox" name="activa" value="1" checked id="activa_nueva" class="rounded text-neutral-900 focus:ring-neutral-900 w-4 h-4">
                    <label for="activa_nueva" class="text-sm font-medium text-neutral-700">Plantilla Activa</label>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-all">Guardar Plantilla</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Plantillas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-10">
        @foreach($plantillas as $p)
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-200 overflow-hidden flex flex-col">
                <form action="{{ route('plantillas.update', $p->id) }}" method="POST" class="flex-1 flex flex-col">
                    @csrf
                    @method('PUT')
                    <div class="px-5 py-4 border-b border-neutral-100 flex justify-between items-center bg-[#FAFAFA]">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full {{ $p->activa ? 'bg-green-500' : 'bg-neutral-300' }}"></span>
                            <input type="text" name="nombre" value="{{ $p->nombre }}" class="font-bold text-neutral-900 bg-transparent border-b border-transparent focus:border-neutral-300 focus:outline-none px-1 py-0.5">
                        </div>
                        <span class="text-xs font-mono bg-neutral-200 text-neutral-700 px-2 py-1 rounded-md">{{ $p->evento }}</span>
                        <input type="hidden" name="evento" value="{{ $p->evento }}">
                    </div>
                    <div class="p-5 flex-1 flex flex-col space-y-4">
                        <textarea name="contenido" rows="5" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none flex-1">{{ $p->contenido }}</textarea>
                        
                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="activa" value="1" {{ $p->activa ? 'checked' : '' }} id="activa_{{ $p->id }}" class="rounded text-neutral-900 focus:ring-neutral-900 w-4 h-4">
                                <label for="activa_{{ $p->id }}" class="text-sm font-medium text-neutral-700">Activa</label>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-neutral-100 text-neutral-700 text-sm font-bold rounded-lg hover:bg-neutral-200 transition-all">Actualizar</button>
                                <button type="button" onclick="if(confirm('¿Eliminar plantilla?')) document.getElementById('del-{{$p->id}}').submit()" class="px-3 py-2 bg-red-50 text-red-600 text-sm font-bold rounded-lg hover:bg-red-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="del-{{$p->id}}" action="{{ route('plantillas.destroy', $p->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
