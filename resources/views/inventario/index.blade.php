@extends('layouts.app')

@section('header_title', 'Inventario')

@section('content')
<div
    x-data="inventarioApp()"
    x-init="init()"
    class="space-y-6"
>

{{-- ══════════════════════════════════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════════════════════════════════ --}}
<div class="flex items-start justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Inventario</h2>
        <p class="text-neutral-500 text-sm mt-1">Productos base y sus variantes con stock físico / reservado / disponible.</p>
    </div>
    <div class="flex items-center gap-2">
        <button @click="abrirModalCategoria()"
                class="px-4 py-2.5 text-sm font-medium border border-neutral-200 rounded-xl hover:bg-neutral-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 9.414V5a2 2 0 012-2zm0 0v4"/>
            </svg>
            Categoría
        </button>
        <button @click="abrirModalProducto()"
                class="px-4 py-2.5 bg-neutral-900 text-white text-sm font-medium rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Producto
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     FILTROS / BUSCADOR
══════════════════════════════════════════════════════════════════ --}}
<div class="flex items-center gap-3">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" x-model="busqueda" @input="filtrar()"
               placeholder="Buscar producto o SKU..."
               class="w-full pl-10 pr-4 py-2.5 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:border-neutral-400 bg-white transition-colors"/>
    </div>
    <select x-model="filtroCategoria" @change="filtrar()"
            class="border border-neutral-200 rounded-xl px-3 py-2.5 text-sm text-neutral-700 focus:outline-none focus:border-neutral-400 bg-white transition-colors">
        <option value="">Todas las categorías</option>
        <template x-for="cat in categorias" :key="cat.id">
            <option :value="cat.nombre" x-text="cat.nombre"></option>
        </template>
    </select>
    <div class="text-sm text-neutral-400" x-text="productosFiltrados.length + ' producto(s)'"></div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TABLA DE PRODUCTOS (Expandible con variantes)
══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white border border-neutral-100 rounded-2xl shadow-sm overflow-hidden">

    {{-- Header de tabla --}}
    <div class="grid grid-cols-12 px-6 py-3 bg-neutral-50 border-b border-neutral-100 text-xs font-semibold text-neutral-500 uppercase tracking-wide">
        <div class="col-span-4">Producto / Variante</div>
        <div class="col-span-2 text-center">Stock Físico</div>
        <div class="col-span-2 text-center">Reservado</div>
        <div class="col-span-2 text-center">Disponible</div>
        <div class="col-span-1 text-right">Precio</div>
        <div class="col-span-1 text-right">Acciones</div>
    </div>

    {{-- Empty state --}}
    <div x-show="productosFiltrados.length === 0" class="py-16 text-center text-neutral-400">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-sm font-medium">Sin productos registrados</p>
        <p class="text-xs mt-1">Haz clic en "Nuevo Producto" para comenzar.</p>
    </div>

    {{-- Filas de productos --}}
    <template x-for="producto in productosFiltrados" :key="producto.id">
        <div class="border-b border-neutral-50 last:border-b-0">

            {{-- Fila padre (el Blank) --}}
            <div class="grid grid-cols-12 items-center px-6 py-4 hover:bg-neutral-50/50 transition-colors cursor-pointer group"
                 @click="toggleProducto(producto.id)">

                <div class="col-span-4 flex items-center gap-3">
                    {{-- Icono expandir/colapsar --}}
                    <div class="w-6 h-6 rounded-lg border border-neutral-200 flex items-center justify-center flex-shrink-0 transition-all"
                         :class="expandidos.includes(producto.id) ? 'bg-neutral-900 border-neutral-900' : 'bg-white group-hover:border-neutral-300'">
                        <svg class="w-3 h-3 transition-transform"
                             :class="{'rotate-90': expandidos.includes(producto.id), 'text-white': expandidos.includes(producto.id), 'text-neutral-400': !expandidos.includes(producto.id)}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    
                    <img :src="obtenerImagenUrl(producto.imagen)"
                         x-on:error="$event.target.src='https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF'"
                         class="w-10 h-10 object-cover rounded-md bg-neutral-50 border border-neutral-100 flex-shrink-0" 
                         alt="Miniatura">

                    <div>
                        <p class="text-sm font-semibold text-neutral-900" x-text="producto.nombre"></p>
                        <p class="text-xs text-neutral-400 mt-0.5" x-text="(producto.categoria?.nombre || 'Sin categoría') + ' · ' + producto.variantes.length + ' variante(s)'"></p>
                    </div>
                </div>

                {{-- Stock agregado del producto --}}
                <div class="col-span-2 text-center">
                    <span class="text-sm font-semibold text-neutral-700"
                          x-text="producto.variantes.reduce((s,v) => s + v.stock_fisico, 0)"></span>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-sm text-amber-600 font-medium"
                          x-text="producto.variantes.reduce((s,v) => s + v.stock_reservado, 0)"></span>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-sm font-bold"
                          :class="producto.variantes.reduce((s,v) => s + Math.max(0, v.stock_fisico - v.stock_reservado), 0) === 0 ? 'text-red-500' : 'text-green-600'"
                          x-text="producto.variantes.reduce((s,v) => s + Math.max(0, v.stock_fisico - v.stock_reservado), 0)"></span>
                </div>
                <div class="col-span-1"></div>

                {{-- Acciones producto --}}
                <div class="col-span-1 flex items-center justify-end gap-1" @click.stop>
                    <button @click="abrirModalVariante(producto)"
                            title="Añadir variante"
                            class="w-7 h-7 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-neutral-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    <button @click="abrirModalProducto(producto)"
                            title="Editar producto"
                            class="w-7 h-7 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-neutral-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Filas hijas (Variantes) --}}
            <div x-show="expandidos.includes(producto.id)"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <template x-for="variante in producto.variantes" :key="variante.id">
                    <div class="grid grid-cols-12 items-center pl-16 pr-6 py-3 bg-neutral-50/30 border-t border-neutral-50 hover:bg-neutral-50 transition-colors">

                        {{-- Nombre variante --}}
                        <div class="col-span-4 pl-12">
                            <div class="flex items-center gap-3">
                                <img :src="obtenerImagenUrl(variante.imagen || producto.imagen)"
                                     x-on:error="$event.target.src='https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF'"
                                     class="w-8 h-8 object-cover rounded-md bg-neutral-50 border border-neutral-100 flex-shrink-0" 
                                     alt="Miniatura">

                                <div>
                                    <span class="font-mono text-xs bg-neutral-100 text-neutral-500 px-2 py-0.5 rounded-md" x-text="variante.sku"></span>
                                    <template x-if="variante.atributos && Object.keys(variante.atributos).length > 0">
                                        <div class="flex gap-1 flex-wrap mt-1">
                                            <template x-for="[key, val] in Object.entries(variante.atributos)" :key="key">
                                                <span class="text-[10px] bg-white border border-neutral-200 text-neutral-500 px-1.5 py-0.5 rounded-md leading-none"
                                                      x-text="val"></span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Stock físico --}}
                        <div class="col-span-2 text-center">
                            @if(auth()->id() === 1)
                            <button @click="abrirModalStock(variante, producto.nombre)"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-neutral-700 hover:text-neutral-900 hover:bg-white border border-transparent hover:border-neutral-200 px-2 py-1 rounded-lg transition-all"
                                    title="Ajustar stock">
                                <span x-text="variante.stock_fisico"></span>
                                <svg class="w-3 h-3 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @else
                            <span class="text-sm font-semibold text-neutral-700" x-text="variante.stock_fisico"></span>
                            @endif
                        </div>

                        {{-- Reservado --}}
                        <div class="col-span-2 text-center">
                            <span class="text-sm"
                                  :class="variante.stock_reservado > 0 ? 'text-amber-600 font-medium' : 'text-neutral-400'"
                                  x-text="variante.stock_reservado"></span>
                        </div>

                        {{-- Disponible --}}
                        <div class="col-span-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="Math.max(0, variante.stock_fisico - variante.stock_reservado) <= variante.stock_minimo && variante.stock_minimo > 0
                                      ? 'bg-red-100 text-red-700'
                                      : Math.max(0, variante.stock_fisico - variante.stock_reservado) === 0
                                          ? 'bg-neutral-100 text-neutral-500'
                                          : 'bg-green-100 text-green-700'"
                                  x-text="Math.max(0, variante.stock_fisico - variante.stock_reservado)">
                            </span>
                        </div>

                        {{-- Precio --}}
                        <div class="col-span-1 text-right">
                            <span class="text-sm font-semibold text-neutral-900" x-text="'L.' + Number(variante.precio).toFixed(2)"></span>
                            @if(auth()->id() === 1)
                            <p class="text-xs text-neutral-400" x-text="'Costo: L.' + Number(variante.costo).toFixed(2)"></p>
                            @endif
                        </div>

                        {{-- Acciones variante --}}
                        <div class="col-span-1 flex items-center justify-end gap-1">
                            <button @click="abrirModalEditVariante(variante, producto)"
                                    title="Editar variante"
                                    class="w-7 h-7 rounded-lg hover:bg-neutral-200 flex items-center justify-center text-neutral-400 hover:text-neutral-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Botón añadir variante dentro del expandido --}}
                <div class="pl-16 pr-6 py-3 border-t border-neutral-50">
                    <button @click="abrirModalVariante(producto)"
                            class="text-xs text-neutral-400 hover:text-neutral-700 flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir variante a <span class="font-medium" x-text="producto.nombre"></span>
                    </button>
                </div>
            </div>

        </div>
    </template>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TOAST de éxito / error
══════════════════════════════════════════════════════════════════ --}}
<div x-show="toast.visible" x-cloak x-transition
     class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl text-sm font-medium"
     :class="toast.tipo === 'ok' ? 'bg-neutral-900 text-white' : 'bg-red-600 text-white'">
    <span x-text="toast.mensaje"></span>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: NUEVO / EDITAR PRODUCTO
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalProducto" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalProducto = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-neutral-100"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-neutral-900" x-text="formProducto.id ? 'Editar Producto' : 'Nuevo Producto Base'"></h3>
            <button @click="modalProducto = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-7 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nombre del producto *</label>
                <input type="text" x-model="formProducto.nombre" placeholder="Ej. Camisa Oversize, Taza Sublimable"
                       class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Categoría</label>
                <select x-model="formProducto.categoria_id"
                        class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 bg-white transition-colors">
                    <option value="">Sin categoría</option>
                    <template x-for="cat in categorias" :key="cat.id">
                        <option :value="cat.id" x-text="cat.nombre"></option>
                    </template>
                </select>
            </div>

            {{-- Componente Imagen Híbrida (Producto) --}}
            <div x-data="{ tipoImagen: formProducto.imagen ? 'url' : 'url' }" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-neutral-700">Imagen del Producto</label>
                    <div class="flex bg-neutral-100 rounded-lg p-0.5">
                        <button type="button" @click="tipoImagen = 'url'"
                                :class="tipoImagen === 'url' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-all">URL</button>
                        <button type="button" @click="tipoImagen = 'archivo'"
                                :class="tipoImagen === 'archivo' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-all">Archivo</button>
                    </div>
                </div>
                <div x-show="tipoImagen === 'url'" x-transition>
                    <input type="url" x-model="formProducto.imagen" placeholder="https://ejemplo.com/imagen.jpg"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
                <div x-show="tipoImagen === 'archivo'" x-transition>
                    <div class="flex items-center justify-center w-full relative">
                        <label class="flex flex-col items-center justify-center w-full h-20 border border-neutral-200 border-dashed rounded-xl cursor-pointer bg-neutral-50 hover:bg-neutral-100 hover:border-neutral-300 transition-all overflow-hidden relative">
                            <template x-if="formProducto.imagen">
                                <img :src="obtenerImagenUrl(formProducto.imagen)" 
                                     x-on:error="$event.target.src='https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF'"
                                     class="absolute inset-0 w-full h-full object-cover opacity-40">
                            </template>
                            <div class="flex flex-col items-center justify-center relative z-10">
                                <svg x-show="subiendoImagen" class="w-5 h-5 animate-spin text-neutral-600 mb-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <svg x-show="!subiendoImagen" class="w-5 h-5 text-neutral-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-[11px] font-semibold text-neutral-700" x-text="subiendoImagen ? 'Subiendo...' : (formProducto.imagen ? 'Cambiar imagen' : 'Click para subir')"></p>
                            </div>
                            <input type="file" class="hidden" accept="image/*" @change="subirImagen($event, 'producto')" :disabled="subiendoImagen"/>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Descripción</label>
                <textarea x-model="formProducto.descripcion" rows="2" placeholder="Descripción general del blank..."
                          class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors resize-none"></textarea>
            </div>
            <div x-show="errorForm" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorForm"></div>
            <button @click="guardarProducto()" :disabled="guardando"
                    class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                <svg x-show="guardando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="guardando ? 'Guardando...' : (formProducto.id ? 'Guardar Cambios' : 'Crear Producto')"></span>
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: NUEVA / EDITAR VARIANTE
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalVariante" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalVariante = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg border border-neutral-100"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-900" x-text="formVariante.id ? 'Editar Variante' : 'Nueva Variante'"></h3>
                <p class="text-xs text-neutral-400 mt-0.5" x-text="'Para: ' + (productoActivo?.nombre ?? '')"></p>
            </div>
            <button @click="modalVariante = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-7 space-y-5">

            {{-- Atributos dinámicos (JSON) --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-neutral-700">Atributos</label>
                    <button @click="addAtributo()" type="button"
                            class="text-xs text-neutral-500 hover:text-neutral-900 flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir atributo
                    </button>
                </div>
                <div class="space-y-2">
                    <template x-for="(attr, i) in formVariante.atributos" :key="i">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="attr.key" @input="generarSkuSugerido()" placeholder="Nombre (ej: color)"
                                   class="flex-1 border border-neutral-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-neutral-400"/>
                            <input type="text" x-model="attr.val" @input="generarSkuSugerido()" placeholder="Valor (ej: Negro)"
                                   class="flex-1 border border-neutral-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-neutral-400"/>
                            <button @click="formVariante.atributos.splice(i, 1); generarSkuSugerido()"
                                    class="w-8 h-8 flex items-center justify-center text-neutral-300 hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="formVariante.atributos.length === 0" class="text-xs text-neutral-400 py-2">
                        Sin atributos. Los atributos definen la variante (color, talla, modelo, etc.)
                    </p>
                </div>
            </div>

            {{-- SKU --}}
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">
                    SKU *
                    <span class="text-xs font-normal text-neutral-400 ml-1">(auto-generado, editable)</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" x-model="formVariante.sku" placeholder="CAM-NEG-M"
                           class="flex-1 border border-neutral-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-neutral-400 uppercase transition-colors"/>
                    <button @click="generarSkuSugerido()" type="button"
                            class="px-3 py-2 border border-neutral-200 rounded-xl text-xs text-neutral-500 hover:bg-neutral-50 transition-colors whitespace-nowrap">
                        ↻ Regenerar
                    </button>
                </div>
            </div>

            {{-- Componente Imagen Híbrida (Variante) --}}
            <div x-data="{ tipoImagen: formVariante.imagen ? 'url' : 'url' }" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-neutral-700">Imagen de la Variante <span class="font-normal text-neutral-400 ml-1">(Opcional)</span></label>
                    <div class="flex bg-neutral-100 rounded-lg p-0.5">
                        <button type="button" @click="tipoImagen = 'url'"
                                :class="tipoImagen === 'url' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-all">URL</button>
                        <button type="button" @click="tipoImagen = 'archivo'"
                                :class="tipoImagen === 'archivo' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-all">Archivo</button>
                    </div>
                </div>
                <div x-show="tipoImagen === 'url'" x-transition>
                    <input type="url" x-model="formVariante.imagen" placeholder="URL (Si se deja vacío, usa la del producto)"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
                <div x-show="tipoImagen === 'archivo'" x-transition>
                    <div class="flex items-center justify-center w-full relative">
                        <label class="flex flex-col items-center justify-center w-full h-20 border border-neutral-200 border-dashed rounded-xl cursor-pointer bg-neutral-50 hover:bg-neutral-100 hover:border-neutral-300 transition-all overflow-hidden relative">
                            <template x-if="formVariante.imagen">
                                <img :src="obtenerImagenUrl(formVariante.imagen)"
                                     x-on:error="$event.target.src='https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF'"
                                     class="absolute inset-0 w-full h-full object-cover opacity-40">
                            </template>
                            <div class="flex flex-col items-center justify-center relative z-10">
                                <svg x-show="subiendoImagen" class="w-5 h-5 animate-spin text-neutral-600 mb-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <svg x-show="!subiendoImagen" class="w-5 h-5 text-neutral-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-[11px] font-semibold text-neutral-700" x-text="subiendoImagen ? 'Subiendo...' : (formVariante.imagen ? 'Cambiar imagen' : 'Click para subir')"></p>
                            </div>
                            <input type="file" class="hidden" accept="image/*" @change="subirImagen($event, 'variante')" :disabled="subiendoImagen"/>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Costo y Precio --}}
            <div class="grid @if(auth()->id() === 1) grid-cols-2 @else grid-cols-1 @endif gap-4">
                @if(auth()->id() === 1)
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Costo (L.)</label>
                    <input type="number" x-model.number="formVariante.costo" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Precio de venta (L.) *</label>
                    <input type="number" x-model.number="formVariante.precio" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
            </div>

            {{-- Stock inicial (solo al crear) --}}
            <div x-show="!formVariante.id" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Stock inicial</label>
                    <input type="number" x-model.number="formVariante.stock_fisico" min="0" step="1" placeholder="0"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Stock mínimo (alerta)</label>
                    <input type="number" x-model.number="formVariante.stock_minimo" min="0" step="1" placeholder="0"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
                </div>
            </div>

            <div x-show="errorForm" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorForm"></div>

            <button @click="guardarVariante()" :disabled="guardando"
                    class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                <svg x-show="guardando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="guardando ? 'Guardando...' : (formVariante.id ? 'Guardar Cambios' : 'Crear Variante')"></span>
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: AJUSTAR STOCK
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalStock" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalStock = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-neutral-100"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="px-7 py-5 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900">Ajustar Stock Físico</h3>
            <p class="text-xs text-neutral-400 mt-0.5" x-text="varianteStock?.sku"></p>
        </div>

        <div class="p-7 space-y-5">
            {{-- Stock actual --}}
            <div class="bg-neutral-50 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-neutral-500 font-medium">Stock físico actual</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1" x-text="varianteStock?.stock_fisico"></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-neutral-500">Reservado</p>
                    <p class="text-lg font-semibold text-amber-600" x-text="varianteStock?.stock_reservado"></p>
                    <p class="text-xs text-neutral-500 mt-1">Disponible</p>
                    <p class="text-lg font-bold text-green-600"
                       x-text="Math.max(0, (varianteStock?.stock_fisico ?? 0) - (varianteStock?.stock_reservado ?? 0))"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">
                    Cantidad a ajustar
                    <span class="text-xs font-normal text-neutral-400">(+ entrada / − merma)</span>
                </label>
                <div class="flex items-center justify-center space-x-4">
                    <button @click="ajusteStock.cantidad = Math.max(-999, ajusteStock.cantidad - 1)"
                            class="w-11 h-11 border border-neutral-200 rounded-xl flex items-center justify-center text-xl font-bold text-neutral-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-all">−</button>
                    <input type="number" x-model.number="ajusteStock.cantidad"
                           class="w-32 text-center text-xl font-bold rounded-lg border border-gray-300 bg-white px-4 py-2 focus:ring-2 focus:ring-gray-900 focus:border-transparent appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"/>
                    <button @click="ajusteStock.cantidad++"
                            class="w-11 h-11 border border-neutral-200 rounded-xl flex items-center justify-center text-xl font-bold text-neutral-500 hover:bg-green-50 hover:border-green-200 hover:text-green-600 transition-all">+</button>
                </div>
                {{-- Preview del nuevo stock --}}
                <p class="text-sm text-neutral-500 mt-2 text-center">
                    Nuevo stock: <span class="font-bold text-neutral-900"
                        x-text="Math.max(0, (varianteStock?.stock_fisico ?? 0) + ajusteStock.cantidad)"></span>
                </p>
            </div>

            <div x-show="errorForm" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorForm"></div>

            <button @click="confirmarAjusteStock()" :disabled="guardando || ajusteStock.cantidad === 0"
                    class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-40 transition-all">
                <span x-text="guardando ? 'Guardando...' : 'Confirmar Ajuste'"></span>
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: NUEVA CATEGORÍA
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalCategoria" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalCategoria = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm border border-neutral-100">
        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
            <h3 class="text-base font-bold">Nueva Categoría</h3>
            <button @click="modalCategoria = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-7 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nombre *</label>
                <input type="text" x-model="formCategoria.nombre" placeholder="Ej. Camisas, Tazas, Cobertores"
                       class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors"/>
            </div>
            <div x-show="errorForm" x-cloak class="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorForm"></div>
            <button @click="guardarCategoria()" :disabled="guardando"
                    class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 transition-all">
                <span x-text="guardando ? 'Guardando...' : 'Crear Categoría'"></span>
            </button>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
function inventarioApp() {
    return {
        // ── Estado ──────────────────────────────────────────────────────────
        todos:             @json($productos),
        categorias:        @json($categorias),   // ← reactivo: se actualiza al crear
        productosFiltrados: [],
        expandidos:        [],
        busqueda:          '',
        filtroCategoria:   '',

        // Modales
        modalProducto:  false,
        modalVariante:  false,
        modalStock:     false,
        modalCategoria: false,

        // Forms
        formProducto:  { id: null, nombre: '', categoria_id: '', descripcion: '', imagen: '' },
        formVariante:  { id: null, producto_id: null, sku: '', atributos: [], costo: 0, precio: 0, stock_fisico: 0, stock_minimo: 0, imagen: '' },
        formCategoria: { nombre: '' },
        ajusteStock:   { cantidad: 0 },

        productoActivo: null,
        varianteStock:  null,

        // UI States
        guardando: false,
        subiendoImagen: false,
        errorForm: '',
        toast: { visible: false, mensaje: '', tipo: 'ok' },

        // ── Init ────────────────────────────────────────────────────────────
        init() {
            this.productosFiltrados = this.todos;
        },

        obtenerImagenUrl(path) {
            if (!path) return 'https://ui-avatars.com/api/?name=P&color=7F9CF5&background=EBF4FF';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:image')) {
                return path;
            }
            let cleanPath = path;
            if (cleanPath.startsWith('/')) {
                cleanPath = cleanPath.substring(1);
            }
            if (cleanPath.startsWith('storage/')) {
                return '/' + cleanPath;
            }
            return '/storage/' + cleanPath;
        },

        // ── Filtros ─────────────────────────────────────────────────────────
        filtrar() {
            const q   = this.busqueda.toLowerCase().trim();
            const cat = this.filtroCategoria.toLowerCase();
            this.productosFiltrados = this.todos.filter(p => {
                const matchNombre = !q || p.nombre.toLowerCase().includes(q)
                    || p.variantes.some(v => v.sku.toLowerCase().includes(q));
                const matchCat = !cat || p.categoria.toLowerCase() === cat;
                return matchNombre && matchCat;
            });
        },

        toggleProducto(id) {
            const idx = this.expandidos.indexOf(id);
            idx === -1 ? this.expandidos.push(id) : this.expandidos.splice(idx, 1);
        },

        // ── Toast ───────────────────────────────────────────────────────────
        mostrarToast(mensaje, tipo = 'ok') {
            this.toast = { visible: true, mensaje, tipo };
            setTimeout(() => this.toast.visible = false, 3000);
        },

        // ── Modal Producto ───────────────────────────────────────────────────
        abrirModalProducto(producto = null) {
            this.errorForm = '';
            this.formProducto = producto
                ? { id: producto.id, nombre: producto.nombre, categoria_id: producto.categoria_id ?? '', descripcion: producto.descripcion ?? '', imagen: producto.imagen || '' }
                : { id: null, nombre: '', categoria_id: '', descripcion: '', imagen: '' };
            this.modalProducto = true;
        },

        async guardarProducto() {
            if (!this.formProducto.nombre.trim()) { this.errorForm = 'El nombre es requerido.'; return; }
            this.guardando = true;
            this.errorForm = '';
            const url    = this.formProducto.id
                ? `/inventario/productos/${this.formProducto.id}`
                : '/inventario/productos';
            const method = this.formProducto.id ? 'PATCH' : 'POST';
            try {
                const res  = await this._fetch(url, method, this.formProducto);
                const data = await res.json();
                if (data.success) {
                    if (this.formProducto.id) {
                        const p = this.todos.find(p => p.id === this.formProducto.id);
                        if (p) { p.nombre = data.producto.nombre; p.categoria = data.producto.categoria?.nombre ?? 'Sin categoría'; }
                    } else {
                        this.todos.push({ ...data.producto, categoria: data.producto.categoria?.nombre ?? 'Sin categoría', variantes: [] });
                    }
                    this.filtrar();
                    this.modalProducto = false;
                    this.mostrarToast(this.formProducto.id ? 'Producto actualizado.' : 'Producto creado correctamente.');
                } else {
                    this.errorForm = data.message || 'Error al guardar.';
                }
            } catch(e) { this.errorForm = 'Error de conexión.'; }
            finally { this.guardando = false; }
        },

        async subirImagen(e, formTarget) {
            const file = e.target.files[0];
            if (!file) return;

            this.subiendoImagen = true;
            this.errorForm = '';

            const formData = new FormData();
            formData.append('imagen', file);

            try {
                const res = await fetch('/inventario/upload-imagen', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await res.json();
                
                if (res.ok && data.success) {
                    if (formTarget === 'producto') {
                        this.formProducto.imagen = data.url;
                    } else {
                        this.formVariante.imagen = data.url;
                    }
                } else {
                    // Si hay error de validación de Laravel, viene en data.message
                    this.errorForm = data.message || 'Error al subir la imagen. Verifica que sea menor a 2MB.';
                }
            } catch(error) {
                console.error("Upload error:", error);
                this.errorForm = 'Error de red o la imagen es demasiado grande. (Máximo 2MB)';
            } finally {
                this.subiendoImagen = false;
                e.target.value = ''; // Reset input
            }
        },

        // ── Modal Variante ───────────────────────────────────────────────────
        abrirModalVariante(producto) {
            this.productoActivo = producto;
            this.errorForm = '';
            this.formVariante = { id: null, producto_id: producto.id, sku: '', atributos: [], costo: 0, precio: 0, stock_fisico: 0, stock_minimo: 0, imagen: '' };
            this.modalVariante = true;
        },

        abrirModalEditVariante(variante, producto) {
            this.productoActivo = producto;
            this.errorForm = '';
            const atrs = Object.entries(variante.atributos ?? {}).map(([key, val]) => ({ key, val }));
            this.formVariante = {
                id: variante.id, producto_id: producto.id,
                sku: variante.sku, atributos: atrs,
                costo: variante.costo, precio: variante.precio,
                imagen: variante.imagen || '', stock_minimo: variante.stock_minimo ?? 0
            };
            this.modalVariante = true;
        },

        addAtributo() {
            this.formVariante.atributos.push({ key: '', val: '' });
        },

        async generarSkuSugerido() {
            if (!this.productoActivo || this.formVariante.id) return;
            const vals = this.formVariante.atributos.filter(a => a.val).map(a => a.val);
            const params = new URLSearchParams({ producto_id: this.productoActivo.id, ...Object.fromEntries(vals.map((v, i) => [`atributos[${i}]`, v])) });
            try {
                const res  = await fetch('/inventario/sku-sugerido?' + params);
                const data = await res.json();
                if (data.sku) this.formVariante.sku = data.sku;
            } catch(e) {}
        },

        async guardarVariante() {
            if (!this.formVariante.sku.trim()) { this.errorForm = 'El SKU es requerido.'; return; }
            if (!this.formVariante.precio)     { this.errorForm = 'El precio de venta es requerido.'; return; }
            this.guardando = true;
            this.errorForm = '';

            // Convertir atributos de array [{key,val}] a objeto {key: val}
            const atributosObj = {};
            this.formVariante.atributos.filter(a => a.key && a.val).forEach(a => { atributosObj[a.key] = a.val; });

            const payload = { ...this.formVariante, atributos: atributosObj, sku: this.formVariante.sku.toUpperCase() };
            const url     = this.formVariante.id ? `/inventario/variantes/${this.formVariante.id}` : '/inventario/variantes';
            const method  = this.formVariante.id ? 'PATCH' : 'POST';
            try {
                const res  = await this._fetch(url, method, payload);
                const data = await res.json();
                if (data.success) {
                    const prod = this.todos.find(p => p.id === this.productoActivo.id);
                    if (prod) {
                        if (this.formVariante.id) {
                            const idx = prod.variantes.findIndex(v => v.id === this.formVariante.id);
                            if (idx > -1) prod.variantes[idx] = { ...prod.variantes[idx], ...data.variante, atributos: atributosObj };
                        } else {
                            prod.variantes.push({ ...data.variante, atributos: atributosObj });
                            if (!this.expandidos.includes(prod.id)) this.expandidos.push(prod.id);
                        }
                    }
                    this.filtrar();
                    this.modalVariante = false;
                    this.mostrarToast(this.formVariante.id ? 'Variante actualizada.' : 'Variante creada correctamente.');
                } else {
                    this.errorForm = data.message || 'Error al guardar.';
                }
            } catch(e) { this.errorForm = 'Error de conexión.'; }
            finally { this.guardando = false; }
        },

        // ── Modal Ajuste Stock ───────────────────────────────────────────────
        abrirModalStock(variante, nombreProducto) {
            this.varianteStock = { ...variante, nombreProducto };
            this.ajusteStock = { cantidad: 0 };
            this.errorForm = '';
            this.modalStock = true;
        },

        async confirmarAjusteStock() {
            if (this.ajusteStock.cantidad === 0) return;
            this.guardando = true;
            this.errorForm = '';
            try {
                const res  = await this._fetch(`/inventario/variantes/${this.varianteStock.id}/stock`, 'PATCH', { cantidad: this.ajusteStock.cantidad });
                const data = await res.json();
                if (data.success) {
                    // Actualizar dato local
                    for (const prod of this.todos) {
                        const v = prod.variantes.find(v => v.id === this.varianteStock.id);
                        if (v) { v.stock_fisico = data.stock_fisico; break; }
                    }
                    this.filtrar();
                    this.modalStock = false;
                    this.mostrarToast('Stock ajustado correctamente.');
                } else {
                    this.errorForm = data.message || 'Error al ajustar.';
                }
            } catch(e) { this.errorForm = 'Error de conexión.'; }
            finally { this.guardando = false; }
        },

        // ── Modal Categoría ──────────────────────────────────────────────────
        abrirModalCategoria() {
            this.formCategoria = { nombre: '' };
            this.errorForm = '';
            this.modalCategoria = true;
        },

        async guardarCategoria() {
            if (!this.formCategoria.nombre.trim()) { this.errorForm = 'El nombre es requerido.'; return; }
            this.guardando = true;
            this.errorForm = '';
            try {
                const res  = await this._fetch('/inventario/categorias', 'POST', this.formCategoria);
                const data = await res.json();
                if (data.success) {
                    // Agregar la nueva categoría al estado reactivo — sin recargar
                    this.categorias.push(data.categoria);
                    this.formCategoria = { nombre: '' };
                    this.modalCategoria = false;
                    this.mostrarToast('Categoría "' + data.categoria.nombre + '" creada correctamente.');
                } else {
                    this.errorForm = data.message || 'Error al crear.';
                }
            } catch(e) { this.errorForm = 'Error de conexión.'; }
            finally { this.guardando = false; }
        },

        // ── Helper fetch con CSRF ────────────────────────────────────────────
        _fetch(url, method, body) {
            return fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(body),
            });
        },
    };
}
</script>
@endpush
