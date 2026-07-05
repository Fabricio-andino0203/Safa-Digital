@extends('layouts.app')

@section('header_title', 'Órdenes de Compra')

@section('content')
<div x-data="{
    modalNuevaCompra: false,
    modalProveedor: false,
    modalValorar: false,
    modalEditarCompra: false,
    buscadorProducto: '',
    contextoCompra: 'nueva',
    proveedores: @js($proveedores),
    variantes: @js($variantes),
    compraForm: {
        proveedor_id: '',
        fecha: '{{ now()->toDateString() }}',
        notas: '',
        detalles: []
    },
    proveedorForm: {
        nombre: '',
        empresa: '',
        telefono: ''
    },
    compraAValorar: null,
    detallesAValorar: [],
    compraAEditar: null,
    editarForm: {
        id: '',
        proveedor_id: '',
        fecha: '',
        notas: '',
        detalles: []
    },
    
    abrirNuevaCompra() {
        this.compraForm = {
            proveedor_id: '',
            fecha: '{{ now()->toDateString() }}',
            notas: '',
            detalles: []
        };
        this.buscadorProducto = '';
        this.contextoCompra = 'nueva';
        this.modalNuevaCompra = true;
    },

    seleccionarProductoParaCompra(detail) {
        const variante = {
            id: detail.producto_variante_id,
            sku: detail.sku || '',
            nombre_completo: detail.nombre_completo || ''
        };
        if (this.contextoCompra === 'editar') {
            this.agregarDetalleEditar(variante);
        } else {
            this.agregarDetalle(variante);
        }
    },
    
    agregarDetalle(variante) {
        let existe = this.compraForm.detalles.find(d => d.producto_variante_id === variante.id);
        if (existe) {
            existe.cantidad++;
        } else {
            this.compraForm.detalles.push({
                producto_variante_id: variante.id,
                sku: variante.sku,
                nombre_completo: variante.nombre_completo,
                cantidad: 1
            });
        }
        this.buscadorProducto = '';
    },
    
    removerDetalle(index) {
        this.compraForm.detalles.splice(index, 1);
    },
    
    agregarDetalleEditar(variante) {
        let existe = this.editarForm.detalles.find(d => d.producto_variante_id === variante.id);
        if (existe) {
            existe.cantidad++;
        } else {
            this.editarForm.detalles.push({
                producto_variante_id: variante.id,
                sku: variante.sku,
                nombre_completo: variante.nombre_completo,
                cantidad: 1
            });
        }
        this.buscadorProducto = '';
    },
    
    removerDetalleEditar(index) {
        this.editarForm.detalles.splice(index, 1);
    },
    
    guardarProveedor() {
        if (!this.proveedorForm.nombre) return;
        
        fetch('/compras/proveedores/quick', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(this.proveedorForm)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.proveedores.push(data.proveedor);
                this.compraForm.proveedor_id = data.proveedor.id;
                this.modalProveedor = false;
                this.proveedorForm = { nombre: '', empresa: '', telefono: '' };
            }
        });
    },
    
    get variantesFiltradas() {
        if (!this.buscadorProducto) return [];
        let query = this.buscadorProducto.toLowerCase();
        return this.variantes.filter(v => 
            v.sku.toLowerCase().includes(query) || 
            v.nombre_completo.toLowerCase().includes(query)
        ).slice(0, 5);
    },

    abrirValorar(compra) {
        this.compraAValorar = compra;
        this.detallesAValorar = compra.detalles.map(d => {
            let nombreProducto = d.variante?.producto?.nombre || 'Producto';
            let atributosStr = '';
            if (d.variante?.attributes && Object.keys(d.variante.attributes).length > 0) {
                atributosStr = ' — ' + Object.values(d.variante.attributes).join(' / ');
            }
            return {
                id: d.id,
                nombre_completo: nombreProducto + atributosStr,
                sku: d.variante?.sku || '',
                cantidad: d.cantidad,
                costo_unitario: d.variante?.costo || 0.00
            };
        });
        this.modalValorar = true;
    },

    abrirEditarCompra(compra) {
        this.compraAEditar = compra;
        this.editarForm = {
            id: compra.id,
            proveedor_id: compra.proveedor_id,
            fecha: compra.fecha.substring(0, 10),
            notas: compra.notas || '',
            detalles: compra.detalles.map(d => {
                let nombreProducto = d.variante?.producto?.nombre || 'Producto';
                let atributosStr = '';
                if (d.variante?.atributos && Object.keys(d.variante.atributos).length > 0) {
                    atributosStr = ' — ' + Object.values(d.variante.atributos).join(' / ');
                }
                return {
                    producto_variante_id: d.producto_variante_id,
                    sku: d.variante?.sku || '',
                    nombre_completo: nombreProducto + atributosStr,
                    cantidad: d.cantidad
                };
            })
        };
        this.buscadorProducto = '';
        this.contextoCompra = 'editar';
        this.modalEditarCompra = true;
    }
}" @producto-seleccionado.window="seleccionarProductoParaCompra($event.detail)" class="max-w-6xl mx-auto space-y-6">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Encabezado y Botón -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-neutral-500">Administra las órdenes de insumos y materias primas con proveedores.</p>
        </div>
        <button @click="abrirNuevaCompra()" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva Orden
        </button>
    </div>

    <!-- Tabla Principal de Órdenes -->
    <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Proveedor</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-sm text-neutral-700">
                    @forelse($compras as $compra)
                    <tr class="hover:bg-neutral-50/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-neutral-900">{{ $compra->numero_orden }}</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-neutral-900">{{ $compra->proveedor->nombre }}</span>
                            @if($compra->proveedor->empresa)
                                <span class="text-xs text-neutral-500 block">{{ $compra->proveedor->empresa }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $compra->fecha->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-bold text-neutral-900">
                            @if($compra->estado === 'Solicitada')
                                <span class="text-neutral-400 italic font-normal">Pendiente Valorar</span>
                            @else
                                L. {{ number_format($compra->total, 2) }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md border
                                {{ $compra->estado === 'Pagada' ? 'bg-green-50 text-green-700 border-green-100' : '' }}
                                {{ $compra->estado === 'Valorizada' ? 'bg-blue-50 text-blue-700 border-blue-100' : '' }}
                                {{ $compra->estado === 'Solicitada' ? 'bg-slate-100 text-slate-700 border-slate-200' : '' }}
                                {{ $compra->estado === 'Cancelada' ? 'bg-red-50 text-red-700 border-red-100' : '' }}
                            ">
                                {{ $compra->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('compras.pdf', $compra->id) }}" target="_blank"
                               class="text-neutral-500 hover:text-neutral-900 font-semibold text-xs transition-colors">
                                Descargar PDF
                            </a>
                            
                            @if($compra->estado === 'Solicitada')
                                <button type="button" @click="abrirEditarCompra({{ $compra->toJson() }})"
                                        class="text-amber-600 hover:text-amber-800 font-semibold text-xs transition-colors">
                                    Editar
                                </button>
                            @endif

                            @if($compra->estado === 'Solicitada' && auth()->id() === 1)
                                <button type="button" @click="abrirValorar({{ $compra->toJson() }})"
                                        class="text-blue-600 hover:text-blue-800 font-semibold text-xs transition-colors">
                                    Valorar Orden
                                </button>
                            @endif

                            @if($compra->estado === 'Valorizada' && (auth()->id() === 1 || auth()->user()->tienePermiso('caja') || auth()->user()->tienePermiso('compras')))
                                <form action="{{ route('compras.recibir', $compra->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Confirmas la recepción y liberación de pago? Se inyectará el stock y se debitará de bancos.')">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 font-semibold text-xs transition-colors">
                                        Liberar Pago y Recibir
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-neutral-400 italic">
                            No se han registrado órdenes de compra.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Nueva Orden de Compra -->
    <div x-show="modalNuevaCompra" class="relative z-50" x-cloak>
        <div x-show="modalNuevaCompra" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalNuevaCompra"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white shadow-2xl p-8 border border-neutral-100 space-y-6">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900">Registrar Solicitud de Compra</h3>
                            <p class="text-xs text-neutral-500">Crea la solicitud de insumos/mercancía (sin precios).</p>
                        </div>
                        <button @click="modalNuevaCompra = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('compras.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Proveedor -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-neutral-700 mb-2">Proveedor *</label>
                                <div class="flex gap-2">
                                    <select name="proveedor_id" x-model="compraForm.proveedor_id" required
                                            class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                        <option value="">Seleccionar Proveedor...</option>
                                        <template x-for="p in proveedores" :key="p.id">
                                            <option :value="p.id" x-text="p.nombre + (p.empresa ? ' ('+p.empresa+')' : '')"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="modalProveedor = true" class="px-3 bg-neutral-100 text-neutral-700 hover:bg-neutral-200 transition-colors rounded-lg flex items-center justify-center" title="Crear Proveedor Rápido">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Fecha -->
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-2">Fecha de Solicitud *</label>
                                <input type="date" name="fecha" x-model="compraForm.fecha" required
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            </div>
                        </div>

                        <!-- Buscador de Productos -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-2">Agregar Producto / Variante</label>
                            <button type="button" @click="contextoCompra = 'nueva'; $dispatch('abrir-buscador-global', { index: compraForm.detalles.length })"
                                    class="w-full text-left rounded-xl border border-neutral-200 px-3 py-2.5 text-sm focus:outline-none bg-white hover:bg-neutral-50 transition-colors flex items-center justify-between">
                                <span class="text-neutral-400 font-semibold">🔍 Buscar Producto en Inventario</span>
                                <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                            </button>
                        </div>

                        <!-- Detalles Agregados -->
                        <div class="border border-neutral-200 rounded-2xl overflow-hidden bg-neutral-50/30">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase">
                                        <th class="px-4 py-3">Producto / Variante</th>
                                        <th class="px-4 py-3 text-center width-24">Cantidad</th>
                                        <th class="px-4 py-3 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(d, index) in compraForm.detalles" :key="index">
                                        <tr class="border-b border-neutral-100 bg-white">
                                            <td class="px-4 py-3">
                                                <span class="font-bold text-neutral-900 block" x-text="d.nombre_completo"></span>
                                                <span class="text-xs text-neutral-500 font-mono" x-text="d.sku"></span>
                                                <input type="hidden" :name="'detalles['+index+'][producto_variante_id]'" :value="d.producto_variante_id">
                                            </td>
                                            <td class="px-4 py-3 flex justify-center">
                                                <input type="number" :name="'detalles['+index+'][cantidad]'" x-model.number="d.cantidad" min="1" required
                                                       class="w-24 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 text-sm text-center text-gray-800 focus:bg-white focus:outline-none">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" @click="removerDetalle(index)" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="compraForm.detalles.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-neutral-400 italic">
                                                Agrega variantes utilizando el buscador de arriba.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-2">Notas / Instrucciones de Envío</label>
                            <textarea name="notas" x-model="compraForm.notas" rows="3" placeholder="Ej. entrega los sábados por la mañana..."
                                      class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalNuevaCompra = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" :disabled="compraForm.detalles.length === 0" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 disabled:bg-neutral-300 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Registrar Solicitud
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Orden de Compra -->
    <div x-show="modalEditarCompra" class="relative z-50" x-cloak>
        <div x-show="modalEditarCompra" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalEditarCompra"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white shadow-2xl p-8 border border-neutral-100 space-y-6">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900">Editar Solicitud de Compra <span x-text="compraAEditar?.numero_orden"></span></h3>
                            <p class="text-xs text-neutral-500">Modifica los detalles o cantidades de la solicitud pendiente.</p>
                        </div>
                        <button @click="modalEditarCompra = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/compras/' + editarForm.id" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Proveedor -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-neutral-700 mb-2">Proveedor *</label>
                                <select name="proveedor_id" x-model="editarForm.proveedor_id" required
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                    <option value="">Seleccionar Proveedor...</option>
                                    <template x-for="p in proveedores" :key="p.id">
                                        <option :value="p.id" x-text="p.nombre + (p.empresa ? ' ('+p.empresa+')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Fecha -->
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-2">Fecha de Solicitud *</label>
                                <input type="date" name="fecha" x-model="editarForm.fecha" required
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            </div>
                        </div>

                        <!-- Buscador de Productos -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-2">Agregar Producto / Variante</label>
                            <button type="button" @click="contextoCompra = 'editar'; $dispatch('abrir-buscador-global', { index: editarForm.detalles.length })"
                                    class="w-full text-left rounded-xl border border-neutral-200 px-3 py-2.5 text-sm focus:outline-none bg-white hover:bg-neutral-50 transition-colors flex items-center justify-between">
                                <span class="text-neutral-400 font-semibold">🔍 Buscar Producto en Inventario</span>
                                <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                            </button>
                        </div>

                        <!-- Detalles Agregados -->
                        <div class="border border-neutral-200 rounded-2xl overflow-hidden bg-neutral-50/30">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase">
                                        <th class="px-4 py-3">Producto / Variante</th>
                                        <th class="px-4 py-3 text-center width-24">Cantidad</th>
                                        <th class="px-4 py-3 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(d, index) in editarForm.detalles" :key="index">
                                        <tr class="border-b border-neutral-100 bg-white">
                                            <td class="px-4 py-3">
                                                <span class="font-bold text-neutral-900 block" x-text="d.nombre_completo"></span>
                                                <span class="text-xs text-neutral-500 font-mono" x-text="d.sku"></span>
                                                <input type="hidden" :name="'detalles['+index+'][producto_variante_id]'" :value="d.producto_variante_id">
                                            </td>
                                            <td class="px-4 py-3 flex justify-center">
                                                <input type="number" :name="'detalles['+index+'][cantidad]'" x-model.number="d.cantidad" min="1" required
                                                       class="w-24 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 text-sm text-center text-gray-800 focus:bg-white focus:outline-none">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" @click="removerDetalleEditar(index)" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="editarForm.detalles.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-8 text-center text-neutral-400 italic">
                                                No hay ítems en esta orden de compra.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-2">Notas / Instrucciones de Envío</label>
                            <textarea name="notas" x-model="editarForm.notas" rows="3" placeholder="Ej. entrega los sábados por la mañana..."
                                      class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalEditarCompra = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" :disabled="editarForm.detalles.length === 0" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 disabled:bg-neutral-300 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Valorar Orden (Administrador ID 1) -->
    <div x-show="modalValorar" class="relative z-50" x-cloak>
        <div x-show="modalValorar" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalValorar"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl p-8 border border-neutral-100 space-y-6">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900">Valorar Orden <span x-text="compraAValorar?.numero_orden"></span></h3>
                            <p class="text-xs text-neutral-500">Asigna el costo unitario final para cada ítem solicitado.</p>
                        </div>
                        <button @click="modalValorar = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/compras/' + compraAValorar?.id + '/valorar'" method="POST" class="space-y-6">
                        @csrf
                        <div class="border border-neutral-200 rounded-2xl overflow-hidden bg-neutral-50/30">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase">
                                        <th class="px-4 py-3">Producto / Variante</th>
                                        <th class="px-4 py-3 text-center">Cant.</th>
                                        <th class="px-4 py-3 width-32">Costo Final (L.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(d, index) in detallesAValorar" :key="index">
                                        <tr class="border-b border-neutral-100 bg-white">
                                            <td class="px-4 py-3">
                                                <span class="font-bold text-neutral-900 block" x-text="d.nombre_completo"></span>
                                                <span class="text-xs text-neutral-500 font-mono" x-text="d.sku"></span>
                                                <input type="hidden" :name="'detalles['+index+'][id]'" :value="d.id">
                                            </td>
                                            <td class="px-4 py-3 text-center font-bold text-neutral-700" x-text="d.cantidad"></td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" :name="'detalles['+index+'][costo_unitario]'" x-model.number="d.costo_unitario" min="0.01" required
                                                       class="w-32 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-center text-gray-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-gray-900">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalValorar = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Guardar Valoración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo Proveedor Rápido -->
    <div x-show="modalProveedor" class="relative z-50" x-cloak>
        <div x-show="modalProveedor" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalProveedor"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalProveedor = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <h3 class="text-lg font-bold text-neutral-900">Nuevo Proveedor</h3>
                        <button @click="modalProveedor = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Nombre Completo *</label>
                            <input type="text" x-model="proveedorForm.nombre" required placeholder="Nombre..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Empresa / Distribuidor</label>
                            <input type="text" x-model="proveedorForm.empresa" placeholder="Ej. Textiles del Norte"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Teléfono</label>
                            <input type="text" x-model="proveedorForm.telefono" placeholder="Ej. 9988-7766"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalProveedor = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="button" @click="guardarProveedor()" :disabled="!proveedorForm.nombre" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 disabled:bg-neutral-300 text-white font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Guardar Proveedor
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Componente Global de Búsqueda de Inventario -->
    <x-buscador-inventario-global />

</div>
@endsection
