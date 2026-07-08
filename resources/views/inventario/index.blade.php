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
        <button @click="modalImportarExcel = true"
                class="px-4 py-2.5 text-sm font-medium border border-neutral-200 rounded-xl hover:bg-neutral-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Importar Excel
        </button>
        <button @click="abrirModalCategoria()"
                class="px-4 py-2.5 text-sm font-medium border border-neutral-200 rounded-xl hover:bg-neutral-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 9.414V5a2 2 0 012-2zm0 0v4"/>
            </svg>
            Categorías
        </button>
        <button @click="abrirModalExtras()"
                class="px-4 py-2.5 text-sm font-medium border border-neutral-200 rounded-xl hover:bg-neutral-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0"/>
            </svg>
            Extras
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
                          x-text="producto.controlar_stock ? producto.variantes.reduce((s,v) => s + v.stock_fisico, 0) : '∞'"></span>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-sm font-medium"
                          :class="producto.controlar_stock && producto.variantes.reduce((s,v) => s + v.stock_reservado, 0) > 0 ? 'text-amber-600 font-medium' : 'text-neutral-400'"
                          x-text="producto.controlar_stock ? producto.variantes.reduce((s,v) => s + v.stock_reservado, 0) : '-'"></span>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-sm font-bold"
                          :class="!producto.controlar_stock ? 'text-blue-600' : (producto.variantes.reduce((s,v) => s + Math.max(0, v.stock_fisico - v.stock_reservado), 0) === 0 ? 'text-red-500' : 'text-green-600')"
                          x-text="producto.controlar_stock ? producto.variantes.reduce((s,v) => s + Math.max(0, v.stock_fisico - v.stock_reservado), 0) : 'Bajo Pedido'"></span>
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
                    <button @click="eliminarProducto(producto)"
                            title="Eliminar producto"
                            class="w-7 h-7 rounded-lg hover:bg-red-50 flex items-center justify-center text-neutral-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
                            <template x-if="producto.controlar_stock">
                                <div>
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
                            </template>
                            <template x-if="!producto.controlar_stock">
                                <span class="text-sm text-neutral-400 font-medium">∞</span>
                            </template>
                        </div>

                        {{-- Reservado --}}
                        <div class="col-span-2 text-center">
                            <span class="text-sm"
                                  :class="producto.controlar_stock && variante.stock_reservado > 0 ? 'text-amber-600 font-medium' : 'text-neutral-400'"
                                  x-text="producto.controlar_stock ? variante.stock_reservado : '-'"></span>
                        </div>

                        {{-- Disponible --}}
                        <div class="col-span-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="!producto.controlar_stock 
                                      ? 'bg-blue-50 text-blue-700 border border-blue-100'
                                      : (Math.max(0, variante.stock_fisico - variante.stock_reservado) <= variante.stock_minimo && variante.stock_minimo > 0
                                          ? 'bg-red-100 text-red-700'
                                          : Math.max(0, variante.stock_fisico - variante.stock_reservado) === 0
                                              ? 'bg-neutral-100 text-neutral-500'
                                              : 'bg-green-100 text-green-700')"
                                  x-text="producto.controlar_stock ? Math.max(0, variante.stock_fisico - variante.stock_reservado) : 'Bajo Pedido'">
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
                            <button @click="eliminarVariante(variante, producto)"
                                    title="Eliminar variante"
                                    class="w-7 h-7 rounded-lg hover:bg-red-50 flex items-center justify-center text-neutral-400 hover:text-red-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

            {{-- Checkbox: Controlar Stock --}}
            <div class="flex items-center gap-2 py-1">
                <input type="checkbox" x-model="formProducto.controlar_stock" id="controlar_stock"
                       class="rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900"/>
                <label for="controlar_stock" class="text-sm font-semibold text-neutral-700 select-none cursor-pointer">
                    Controlar Stock en Inventario
                </label>
            </div>

            {{-- Sección de Extras del Producto --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between border-t border-neutral-100 pt-3">
                    <label class="block text-sm font-semibold text-neutral-700">Extras (DTF, Sublimación, etc.)</label>
                    <button type="button" @click="formProducto.extras.push({ nombre: '', costo: 0, precio: 0 })"
                            class="px-2.5 py-1 bg-neutral-900 text-white text-[10px] font-bold rounded-lg hover:bg-neutral-800 transition-colors"
                            x-text="obtenerExtrasHeredados().length > 0 ? '+ Agregar Extra Específico' : '+ Agregar Extra'">
                    </button>
                </div>
                
                {{-- Reflejo visual de extras heredados por categoría (Tarea 2) --}}
                <div x-show="obtenerExtrasHeredados().length > 0" class="p-3 bg-green-50/70 border border-green-100 rounded-xl space-y-1.5">
                    <p class="text-[11px] font-bold text-green-800 flex items-center gap-1">
                        <span>✅ Extras heredados automáticamente:</span>
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="ex in obtenerExtrasHeredados()" :key="ex.id">
                            <span class="text-[10px] font-semibold bg-green-100 text-green-800 px-2 py-0.5 rounded-lg animate-pulse"
                                  x-text="ex.nombre + ' (L. ' + Number(ex.precio).toFixed(2) + ')'"></span>
                        </template>
                    </div>
                </div>

                <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                    <template x-for="(extra, index) in formProducto.extras" :key="index">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="extra.nombre" placeholder="Nombre (ej. Estampado DTF)" required
                                   class="flex-1 border border-neutral-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:border-neutral-400 bg-white"/>
                            
                            <div class="w-20 relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] text-neutral-400 font-semibold">C:</span>
                                <input type="number" x-model.number="extra.costo" step="0.01" min="0" placeholder="Costo" required
                                       class="w-full pl-5 pr-1 py-1.5 border border-neutral-200 rounded-xl text-xs text-right focus:outline-none focus:border-neutral-400 bg-white"/>
                            </div>
                            
                            <div class="w-20 relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] text-neutral-400 font-semibold">P:</span>
                                <input type="number" x-model.number="extra.precio" step="0.01" min="0" placeholder="Precio" required
                                       class="w-full pl-5 pr-1 py-1.5 border border-neutral-200 rounded-xl text-xs text-right focus:outline-none focus:border-neutral-400 bg-white"/>
                            </div>

                            <button type="button" @click="formProducto.extras.splice(index, 1)"
                                    class="p-1.5 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="(!formProducto.extras || formProducto.extras.length === 0) && obtenerExtrasHeredados().length === 0">
                        <p class="text-[11px] text-neutral-400 text-center italic py-2">Sin extras configurados.</p>
                    </template>
                </div>
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
            <div x-show="!formVariante.id && (productoActivo?.controlar_stock ?? true)" class="grid grid-cols-2 gap-4">
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
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-neutral-100 overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
            <h3 class="text-base font-bold text-neutral-900" x-text="formCategoria.id ? 'Editar Categoría' : 'Nueva Categoría'"></h3>
            <button @click="modalCategoria = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex flex-1 overflow-hidden divide-x divide-neutral-150">
            <!-- Columna Izquierda: Formulario -->
            <div class="w-1/2 p-6 overflow-y-auto space-y-4 flex flex-col">
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 mb-1.5 uppercase tracking-wider">Nombre de Categoría *</label>
                    <input type="text" x-model="formCategoria.nombre" placeholder="Ej. Camisas, Tazas, Cobertores"
                           class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-900 bg-[#FAFAFA] focus:bg-white transition-colors"/>
                </div>

                <!-- Extras Predeterminados -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider">Extras Predeterminados (Herencia)</label>
                    <div class="bg-neutral-50 border border-neutral-200/65 rounded-2xl p-4 max-h-[200px] overflow-y-auto space-y-2">
                        <template x-for="ext in extrasList" :key="ext.id">
                            <label class="flex items-center gap-3 px-1 py-0.5 cursor-pointer select-none group">
                                <input type="checkbox" :value="String(ext.id)" x-model="formCategoria.extras"
                                       class="rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950 w-4 h-4"/>
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-bold text-neutral-800 block" x-text="ext.nombre"></span>
                                    <span class="text-[10px] text-neutral-400 font-semibold" x-text="'L. ' + Number(ext.precio).toFixed(2)"></span>
                                </div>
                            </label>
                        </template>
                        <template x-if="extrasList.length === 0">
                            <div class="text-center text-xs text-neutral-400 italic py-4">No hay extras configurados en el sistema.</div>
                        </template>
                    </div>
                </div>

                <div x-show="errorForm" x-cloak class="text-xs text-red-650 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorForm"></div>

                <div class="pt-4 flex gap-2">
                    <template x-if="formCategoria.id">
                        <button type="button" @click="cancelarEdicionCategoria()"
                                class="flex-1 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold rounded-2xl text-xs transition-all">
                            Cancelar
                        </button>
                    </template>
                    <button @click="guardarCategoria()" :disabled="guardando"
                            class="flex-2 py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 transition-all text-xs flex-1">
                        <span x-text="guardando ? 'Guardando...' : (formCategoria.id ? 'Guardar Cambios' : 'Crear Categoría')"></span>
                    </button>
                </div>
            </div>

            <!-- Columna Derecha: Categorías Existentes -->
            <div class="w-1/2 p-6 bg-neutral-50/50 overflow-y-auto flex flex-col">
                <label class="block text-xs font-semibold text-neutral-500 mb-3 uppercase tracking-wider">Categorías Existentes</label>
                <div class="space-y-2 flex-1 overflow-y-auto">
                    <template x-for="cat in categorias" :key="cat.id">
                        <div class="p-3 bg-white border border-neutral-100 rounded-2xl shadow-sm flex items-center justify-between gap-3 group">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold text-neutral-800 block truncate" x-text="cat.nombre"></span>
                                <!-- Extras configurados en la categoría -->
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <template x-for="ex in cat.extras" :key="ex.id">
                                        <span class="px-1.5 py-0.5 bg-neutral-100 border border-neutral-200 rounded text-[9px] font-medium text-neutral-600" x-text="ex.nombre"></span>
                                    </template>
                                    <template x-if="!cat.extras || cat.extras.length === 0">
                                        <span class="text-[9px] text-neutral-400 italic">Sin extras heredados</span>
                                    </template>
                                </div>
                            </div>
                            <button type="button" @click="cargarCategoriaParaEditar(cat)"
                                    class="px-2.5 py-1.5 bg-neutral-100 hover:bg-neutral-900 hover:text-white text-neutral-700 text-[10px] font-bold rounded-lg transition-all flex-shrink-0">
                                Editar
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: GESTOR DE EXTRAS
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalExtras" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalExtras = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl border border-neutral-100 overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
            <h3 class="text-base font-bold text-neutral-900" x-text="formExtra.id ? 'Editar Extra Base' : 'Nuevo Extra Base'"></h3>
            <button @click="modalExtras = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="flex flex-1 overflow-hidden divide-x divide-neutral-150">
            <!-- Columna Izquierda: Formulario -->
            <div class="w-1/2 p-6 overflow-y-auto space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 mb-1.5 uppercase tracking-wider">Nombre del Extra *</label>
                        <input type="text" x-model="formExtra.nombre" placeholder="Ej. Vinil Textil, DTF A4, Bordado"
                               class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-900 bg-[#FAFAFA] focus:bg-white transition-colors"/>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 mb-1.5 uppercase tracking-wider">Costo (L.) *</label>
                            <input type="number" step="0.01" x-model="formExtra.costo" placeholder="0.00"
                                   class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-900 bg-[#FAFAFA] focus:bg-white transition-colors"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 mb-1.5 uppercase tracking-wider">Precio Venta (L.) *</label>
                            <input type="number" step="0.01" x-model="formExtra.precio" placeholder="0.00"
                                   class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-900 bg-[#FAFAFA] focus:bg-white transition-colors"/>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-4">
                    <div x-show="errorFormExtra" x-cloak class="text-xs text-red-650 bg-red-50 border border-red-100 rounded-xl px-4 py-3" x-text="errorFormExtra"></div>

                    <div class="flex gap-2">
                        <template x-if="formExtra.id">
                            <button type="button" @click="cancelarEdicionExtra()"
                                    class="flex-1 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold rounded-2xl text-xs transition-all">
                                Cancelar
                            </button>
                        </template>
                        <button @click="guardarExtra()" :disabled="guardandoExtra"
                                class="flex-2 py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 disabled:opacity-50 transition-all text-xs flex-1">
                            <span x-text="guardandoExtra ? 'Guardando...' : (formExtra.id ? 'Guardar Cambios' : 'Crear Extra')"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Extras Existentes -->
            <div class="w-1/2 p-6 bg-neutral-50/50 overflow-y-auto flex flex-col">
                <label class="block text-xs font-semibold text-neutral-500 mb-3 uppercase tracking-wider">Extras Base Activos</label>
                <div class="space-y-2 flex-1 overflow-y-auto">
                    <template x-for="ext in extrasList" :key="ext.id">
                        <div class="p-3 bg-white border border-neutral-100 rounded-2xl shadow-sm flex items-center justify-between gap-3 group">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold text-neutral-800 block truncate" x-text="ext.nombre"></span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] text-neutral-400 font-semibold" x-text="'Costo: L. ' + Number(ext.costo || 0).toFixed(2)"></span>
                                    <span class="text-[10px] text-neutral-500 font-bold" x-text="'Precio: L. ' + Number(ext.precio || 0).toFixed(2)"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button type="button" @click="cargarExtraParaEditar(ext)"
                                        class="px-2.5 py-1.5 bg-neutral-100 hover:bg-neutral-900 hover:text-white text-neutral-700 text-[10px] font-bold rounded-lg transition-all">
                                    Editar
                                </button>
                                <button type="button" @click="eliminarExtra(ext)"
                                        class="px-2.5 py-1.5 bg-red-50 hover:bg-red-650 hover:text-white text-red-650 text-[10px] font-bold rounded-lg transition-all border border-red-100/50">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="extrasList.length === 0">
                        <div class="text-center text-xs text-neutral-400 italic py-8">No hay extras base creados en el sistema.</div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL: IMPORTAR EXCEL
══════════════════════════════════════════════════════════════════ --}}
<div x-show="modalImportarExcel" x-cloak
     class="fixed inset-0 z-50 bg-neutral-900/40 backdrop-blur-sm flex items-center justify-center p-4"
     @keydown.escape.window="modalImportarExcel = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-neutral-100">
        <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-neutral-900">Importar Productos desde Excel</h3>
            <button @click="modalImportarExcel = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('inventario.importarExcel') }}" method="POST" enctype="multipart/form-data" class="p-7 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Archivo Excel (.xlsx, .xls, .csv) *</label>
                <input type="file" name="excel_file" accept=".xlsx, .xls, .csv" required
                       class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-neutral-400 transition-colors file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-neutral-900 file:text-white hover:file:bg-neutral-800 cursor-pointer"/>
            </div>
            <div class="text-xs text-neutral-500 bg-neutral-50 rounded-xl p-3 border border-neutral-100 space-y-1">
                <p class="font-semibold text-neutral-700">Columnas requeridas o soportadas en la fila de cabecera:</p>
                <ul class="list-disc list-inside pl-1 space-y-0.5">
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">producto</span> o <span class="font-mono bg-white px-1 py-0.5 border rounded">nombre</span> (Nombre de producto)</li>
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">categoria</span> (Nombre de categoría)</li>
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">sku</span> (Código único)</li>
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">color</span>, <span class="font-mono bg-white px-1 py-0.5 border rounded">talla</span> (Atributos de variante)</li>
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">costo</span>, <span class="font-mono bg-white px-1 py-0.5 border rounded">precio</span></li>
                    <li><span class="font-mono bg-white px-1 py-0.5 border rounded">stock</span> o <span class="font-mono bg-white px-1 py-0.5 border rounded">stock_fisico</span></li>
                </ul>
            </div>
            <button type="submit" class="w-full py-3 bg-neutral-900 text-white font-semibold rounded-2xl hover:bg-neutral-800 transition-all">
                Importar Archivo
            </button>
        </form>
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
        extrasList:        @json($extras),
        productosFiltrados: [],
        expandidos:        [],
        busqueda:          '',
        filtroCategoria:   '',

        // Modales
        modalProducto:  false,
        modalVariante:  false,
        modalStock:     false,
        modalCategoria: false,
        modalExtras:    false,
        modalImportarExcel: false,

        // Forms
        formProducto:  { id: null, nombre: '', categoria_id: '', descripcion: '', imagen: '', extras: [], controlar_stock: true },
        formVariante:  { id: null, producto_id: null, sku: '', atributos: [], costo: 0, precio: 0, stock_fisico: 0, stock_minimo: 0, imagen: '' },
        formCategoria: { id: null, nombre: '', extras: [] },
        formExtra:     { id: null, nombre: '', costo: '', precio: '' },
        ajusteStock:   { cantidad: 0 },

        productoActivo: null,
        varianteStock:  null,

        // UI States
        guardando: false,
        guardandoExtra: false,
        subiendoImagen: false,
        errorForm: '',
        errorFormExtra: '',
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

        obtenerExtrasHeredados() {
            if (!this.formProducto.categoria_id) return [];
            const cat = this.categorias.find(c => c.id == this.formProducto.categoria_id);
            return cat ? (cat.extras || []) : [];
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
                ? { 
                    id: producto.id, 
                    nombre: producto.nombre, 
                    categoria_id: producto.categoria_id ?? '', 
                    descripcion: producto.descripcion ?? '', 
                    imagen: producto.imagen || '',
                    extras: producto.extras ? JSON.parse(JSON.stringify(producto.extras)) : [],
                    controlar_stock: producto.controlar_stock !== undefined ? !!producto.controlar_stock : true
                  }
                : { id: null, nombre: '', categoria_id: '', descripcion: '', imagen: '', extras: [], controlar_stock: true };
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
                        if (p) { 
                            p.nombre = data.producto.nombre; 
                            p.categoria = data.producto.categoria?.nombre ?? 'Sin categoría'; 
                            p.extras = data.producto.extras || [];
                            p.controlar_stock = data.producto.controlar_stock;
                        }
                    } else {
                        this.todos.push({ ...data.producto, categoria: data.producto.categoria?.nombre ?? 'Sin categoría', variantes: [], extras: data.producto.extras || [], controlar_stock: data.producto.controlar_stock });
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
            this.formCategoria = { id: null, nombre: '', extras: [] };
            this.errorForm = '';
            this.modalCategoria = true;
        },

        cargarCategoriaParaEditar(cat) {
            this.formCategoria = {
                id: cat.id,
                nombre: cat.nombre,
                extras: (cat.extras || []).map(e => String(e.id))
            };
            this.errorForm = '';
        },

        cancelarEdicionCategoria() {
            this.formCategoria = { id: null, nombre: '', extras: [] };
            this.errorForm = '';
        },

        async guardarCategoria() {
            if (!this.formCategoria.nombre.trim()) { this.errorForm = 'El nombre es requerido.'; return; }
            this.guardando = true;
            this.errorForm = '';
            try {
                const res  = await this._fetch('/inventario/categorias', 'POST', this.formCategoria);
                const data = await res.json();
                if (data.success) {
                    const idx = this.categorias.findIndex(c => c.id === data.categoria.id);
                    if (idx !== -1) {
                        this.categorias[idx] = data.categoria;
                    } else {
                        this.categorias.push(data.categoria);
                    }
                    this.formCategoria = { id: null, nombre: '', extras: [] };
                    this.modalCategoria = false;
                    this.mostrarToast('Categoría guardada correctamente.');
                } else {
                    this.errorForm = data.message || 'Error al guardar.';
                }
            } catch(e) { this.errorForm = 'Error de conexión.'; }
            finally { this.guardando = false; }
        },

        // ── Modal Extras Base ────────────────────────────────────────────────
        abrirModalExtras() {
            this.formExtra = { id: null, nombre: '', costo: '', precio: '' };
            this.errorFormExtra = '';
            this.modalExtras = true;
        },

        cargarExtraParaEditar(ext) {
            this.formExtra = {
                id: ext.id,
                nombre: ext.nombre,
                costo: ext.costo,
                precio: ext.precio
            };
            this.errorFormExtra = '';
        },

        cancelarEdicionExtra() {
            this.formExtra = { id: null, nombre: '', costo: '', precio: '' };
            this.errorFormExtra = '';
        },

        async guardarExtra() {
            if (!this.formExtra.nombre.trim()) { this.errorFormExtra = 'El nombre es requerido.'; return; }
            if (this.formExtra.costo === '' || this.formExtra.precio === '') { this.errorFormExtra = 'Costo y precio son requeridos.'; return; }
            this.guardandoExtra = true;
            this.errorFormExtra = '';
            try {
                const res  = await this._fetch('/inventario/extras', 'POST', this.formExtra);
                const data = await res.json();
                if (data.success) {
                    const idx = this.extrasList.findIndex(e => e.id === data.extra.id);
                    if (idx !== -1) {
                        this.extrasList[idx] = data.extra;
                    } else {
                        this.extrasList.push(data.extra);
                    }
                    this.formExtra = { id: null, nombre: '', costo: '', precio: '' };
                    this.mostrarToast('Extra guardado correctamente.');
                } else {
                    this.errorFormExtra = data.message || 'Error al guardar.';
                }
            } catch(e) { this.errorFormExtra = 'Error de conexión.'; }
            finally { this.guardandoExtra = false; }
        },

        async eliminarExtra(ext) {
            if (!confirm(`¿Estás seguro de que deseas eliminar el extra "${ext.nombre}"?`)) return;
            try {
                const res = await this._fetch(`/inventario/extras/${ext.id}`, 'DELETE');
                const data = await res.json();
                if (data.success) {
                    this.extrasList = this.extrasList.filter(e => e.id !== ext.id);
                    this.mostrarToast('Extra eliminado correctamente.');
                } else {
                    alert(data.message || 'No se pudo eliminar el extra.');
                }
            } catch (e) {
                this.mostrarToast('Error de conexión al eliminar.', 'error');
            }
        },

        async eliminarProducto(producto) {
            if (!confirm(`¿Estás seguro de que deseas eliminar el producto "${producto.nombre}"? Esto también desactivará sus variantes.`)) {
                return;
            }
            try {
                const res = await this._fetch(`/inventario/productos/${producto.id}`, 'DELETE');
                const data = await res.json();
                if (data.success) {
                    this.todos = this.todos.filter(p => p.id !== producto.id);
                    this.filtrar();
                    this.mostrarToast('Producto eliminado correctamente.');
                } else {
                    alert(data.message || 'No se pudo eliminar el producto.');
                }
            } catch (e) {
                this.mostrarToast('Error de conexión al eliminar.', 'error');
            }
        },

        async eliminarVariante(variante, producto) {
            if (!confirm(`¿Estás seguro de que deseas eliminar la variante "${variante.sku}" del producto "${producto.nombre}"?`)) {
                return;
            }
            try {
                const res = await this._fetch(`/inventario/variantes/${variante.id}`, 'DELETE');
                const data = await res.json();
                if (data.success) {
                    const p = this.todos.find(p => p.id === producto.id);
                    if (p) {
                        p.variantes = p.variantes.filter(v => v.id !== variante.id);
                    }
                    this.filtrar();
                    this.mostrarToast('Variante eliminada correctamente.');
                } else {
                    alert(data.message || 'No se pudo eliminar la variante.');
                }
            } catch (e) {
                this.mostrarToast('Error de conexión al eliminar.', 'error');
            }
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
