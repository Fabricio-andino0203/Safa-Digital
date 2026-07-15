@php
    $categorias = \App\Models\Categoria::with('extras')->orderBy('nombre')->get();
    $productos = \App\Models\Producto::with(['categoria.extras', 'extras', 'variantes' => function($q) {
        $q->where('activo', true)
          ->orderBy('atributos->Color')
          ->orderBy('atributos->Talla');
    }])->where('activo', true)->orderBy('nombre')->get();
@endphp

<div x-data="buscadorInventarioGlobal()"
     @abrir-buscador-global.window="abrir($event.detail.index)"
     x-show="isOpen"
     class="relative z-[100]"
     x-cloak>
    
    <!-- Backdrop -->
    <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.away="cerrar()"
                 class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl border border-neutral-100 flex flex-col max-h-[85vh]">
                 
                 <!-- Header -->
                 <div class="px-7 py-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50 flex-shrink-0">
                     <h3 class="text-base font-bold text-neutral-900" x-text="paso === 1 ? 'Buscar Producto en Inventario' : 'Configurar Variante y Extras'"></h3>
                     <button type="button" @click="cerrar()" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 rounded-xl hover:bg-neutral-100 transition-all">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                     </button>
                 </div>

                 <!-- Contenido Paso 1: Lista de Productos -->
                 <div x-show="paso === 1" class="flex-1 overflow-hidden flex flex-col">
                     <!-- Categorías y Búsqueda -->
                     <div class="p-6 pb-2 space-y-4 flex-shrink-0">
                         <!-- Input de Búsqueda -->
                         <div class="relative">
                             <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                             </svg>
                             <input type="text" x-model="busqueda" placeholder="Buscar producto por nombre o SKU..."
                                    class="w-full pl-10 pr-4 py-3 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:border-neutral-900 bg-[#FAFAFA] focus:bg-white transition-colors"/>
                         </div>

                         <!-- Píldoras de Categorías -->
                         <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                             <button type="button" @click="categoriaId = null"
                                     :class="categoriaId === null ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200'"
                                     class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap">
                                 Todos
                             </button>
                             <template x-for="cat in categorias" :key="cat.id">
                                 <button type="button" @click="categoriaId = cat.id"
                                         :class="categoriaId === cat.id ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200'"
                                         class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap"
                                         x-text="cat.nombre">
                                 </button>
                             </template>
                         </div>
                     </div>

                     <!-- Lista de Productos -->
                     <div class="flex-1 overflow-y-auto px-6 pb-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                         <template x-for="p in productosFiltrados" :key="p.id">
                             <div class="flex items-center gap-3 p-3 rounded-2xl border border-neutral-100 bg-[#FAFAFA] hover:border-neutral-300 hover:bg-white transition-all group">
                                 <img :src="p.imagen || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(p.nombre) + '&color=7F9CF5&background=EBF4FF'"
                                      class="w-12 h-12 object-cover rounded-xl bg-neutral-100 border border-neutral-200/50 flex-shrink-0"
                                      alt="Miniatura">
                                 
                                 <div class="flex-1 min-w-0">
                                     <h4 class="text-xs font-bold text-neutral-800 truncate" x-text="p.nombre"></h4>
                                     <p class="text-[10px] text-neutral-400 font-semibold" x-text="p.categoria?.nombre || 'Sin Categoría'"></p>
                                     <span class="text-[10px] text-neutral-500 font-medium" x-text="p.variantes.length + ' variantes disponibles'"></span>
                                 </div>

                                 <button type="button" @click="irADetalle(p)"
                                         class="px-3 py-2 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm flex-shrink-0">
                                     Seleccionar
                                 </button>
                             </div>
                         </template>

                         <div x-show="productosFiltrados.length === 0" class="col-span-2 text-center py-12 text-neutral-400 text-xs italic">
                             No se encontraron productos en esta categoría.
                         </div>
                     </div>
                 </div>

                 <!-- Contenido Paso 2: Detalles (Variantes y Extras) -->
                 <div x-show="paso === 2" class="flex-1 overflow-y-auto p-6 space-y-6">
                     <!-- Cabecera de Producto -->
                     <div class="flex items-center gap-4 border-b border-neutral-100 pb-4">
                         <button type="button" @click="volverAlPaso1()"
                                 class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1">
                             ← Volver
                         </button>
                         <div>
                             <h4 class="text-sm font-bold text-neutral-900" x-text="productoSeleccionado?.nombre"></h4>
                             <p class="text-[10px] text-neutral-400 font-semibold" x-text="productoSeleccionado?.categoria?.nombre"></p>
                         </div>
                     </div>

                     <!-- Variantes -->
                     <div class="space-y-2">
                         <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider">Selecciona una Variante *</label>
                         <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                             <template x-for="v in productoSeleccionado?.variantes" :key="v.id">
                                 <button type="button" @click="varianteSeleccionada = v"
                                         :class="varianteSeleccionada?.id === v.id ? 'border-neutral-950 bg-neutral-950 text-white ring-2 ring-neutral-950/20' : 'border-neutral-200 bg-white text-neutral-800 hover:border-neutral-400'"
                                         class="p-3 rounded-xl border text-xs font-bold transition-all text-center flex flex-col items-center justify-center gap-2">
                                     <img :src="v.imagen || productoSeleccionado?.imagen || 'https://ui-avatars.com/api/?name=V&color=7F9CF5&background=EBF4FF'"
                                          class="w-10 h-10 object-cover rounded-lg border border-neutral-200/60 shadow-sm"
                                          alt="Miniatura">
                                     <div class="flex flex-col items-center">
                                         <span x-text="v.nombre_completo"></span>
                                         <span class="text-[10px]" :class="varianteSeleccionada?.id === v.id ? 'text-neutral-200' : 'text-neutral-400'" x-text="'SKU: ' + v.sku"></span>
                                         <span class="text-xs font-black mt-1" x-text="'L. ' + Number(v.precio).toFixed(2)"></span>
                                         
                                         {{-- Indicador de Stock --}}
                                         <div class="mt-1 flex items-center justify-center">
                                             <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full border"
                                                   :class="obtenerStockBadge(v).class"
                                                   x-text="obtenerStockBadge(v).text"></span>
                                         </div>
                                     </div>
                                 </button>
                             </template>
                         </div>
                     </div>

                     <!-- Panel de Extras -->
                     <div x-show="extrasDisponibles.length > 0" class="space-y-2">
                         <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider">Extras Disponibles</label>
                         <div class="space-y-1.5">
                             <template x-for="extra in extrasDisponibles" :key="extra.id">
                                 <div class="flex items-center justify-between p-2.5 bg-neutral-50 rounded-xl border border-neutral-200/50 transition-all hover:bg-neutral-100/40">
                                     <div>
                                         <span class="text-[11px] font-bold text-neutral-800 block" x-text="extra.nombre"></span>
                                         <span class="text-[9px] font-semibold text-neutral-400" x-text="'L. ' + Number(extra.precio).toFixed(2) + ' c/u'"></span>
                                     </div>
                                     <div class="flex items-center gap-1.5 bg-white border border-neutral-200 rounded-md p-1 shadow-sm">
                                         <button type="button" @click="quitarExtra(extra)"
                                                 class="w-5 h-5 flex items-center justify-center text-[10px] font-bold bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded transition-colors select-none">
                                             −
                                         </button>
                                         <input type="number" readonly :value="obtenerCantidadExtra(extra.id)"
                                                class="w-6 text-center text-[10px] font-bold text-neutral-800 focus:outline-none border-none bg-transparent select-none"/>
                                         <button type="button" @click="agregarExtra(extra)"
                                                 class="w-5 h-5 flex items-center justify-center text-[10px] font-bold bg-neutral-900 hover:bg-neutral-800 text-white rounded transition-colors select-none">
                                             +
                                         </button>
                                     </div>
                                 </div>
                             </template>
                         </div>
                     </div>

                     <!-- Footer Paso 2 -->
                     <div class="pt-6 border-t border-neutral-100 flex items-center justify-between">
                         <div>
                             <span class="text-[9px] font-bold text-neutral-400 uppercase tracking-wider block">Total Estimado</span>
                             <span class="text-base font-black text-neutral-950" x-text="'L. ' + totalEstimado.toFixed(2)"></span>
                         </div>
                         <button type="button" @click="confirmarSeleccion()"
                                 :disabled="!varianteSeleccionada"
                                 class="px-6 py-3 bg-neutral-900 text-white text-xs font-bold rounded-xl hover:bg-neutral-800 active:scale-95 transition-all shadow-md disabled:opacity-40">
                             Confirmar y Agregar
                         </button>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<script>
    function buscadorInventarioGlobal() {
        return {
            isOpen: false,
            paso: 1,
            indexDestino: null,
            busqueda: '',
            categoriaId: null,

            categorias: @json($categorias),
            productos: @json($productos),

            productoSeleccionado: null,
            varianteSeleccionada: null,
            extrasSeleccionados: [],

            abrir(index) {
                this.indexDestino = index;
                this.paso = 1;
                this.busqueda = '';
                this.categoriaId = null;
                this.productoSeleccionado = null;
                this.varianteSeleccionada = null;
                this.extrasSeleccionados = [];
                this.isOpen = true;
            },

            cerrar() {
                this.isOpen = false;
            },

            get productosFiltrados() {
                let list = this.productos;
                if (this.categoriaId !== null) {
                    list = list.filter(p => p.categoria_id == this.categoriaId);
                }
                if (this.busqueda.trim() !== '') {
                    const term = this.busqueda.toLowerCase().trim();
                    list = list.filter(p => 
                        p.nombre.toLowerCase().includes(term) ||
                        p.variantes.some(v => v.sku.toLowerCase().includes(term) || v.nombre_completo.toLowerCase().includes(term))
                    );
                }
                return list;
            },

            irADetalle(producto) {
                this.productoSeleccionado = producto;
                this.varianteSeleccionada = null;
                this.extrasSeleccionados = [];
                this.paso = 2;
            },

            volverAlPaso1() {
                this.productoSeleccionado = null;
                this.varianteSeleccionada = null;
                this.extrasSeleccionados = [];
                this.paso = 1;
            },

            get extrasDisponibles() {
                if (!this.productoSeleccionado) return [];
                const directos = this.productoSeleccionado.extras || [];
                const heredados = (this.productoSeleccionado.categoria && this.productoSeleccionado.categoria.extras) ? this.productoSeleccionado.categoria.extras : [];
                
                const ids = new Set();
                const unificados = [];
                [...directos, ...heredados].forEach(e => {
                    if (!ids.has(e.id)) {
                        ids.add(e.id);
                        unificados.push(e);
                    }
                });
                return unificados;
            },

            agregarExtra(extra) {
                const found = this.extrasSeleccionados.find(e => e.id === extra.id);
                if (found) {
                    found.cantidad++;
                } else {
                    this.extrasSeleccionados.push({
                        id: extra.id,
                        cantidad: 1,
                        nombre: extra.nombre,
                        precio: parseFloat(extra.precio),
                        costo: parseFloat(extra.costo)
                    });
                }
            },

            quitarExtra(extra) {
                const found = this.extrasSeleccionados.find(e => e.id === extra.id);
                if (found) {
                    found.cantidad--;
                    if (found.cantidad <= 0) {
                        this.extrasSeleccionados = this.extrasSeleccionados.filter(e => e.id !== extra.id);
                    }
                }
            },

            obtenerCantidadExtra(extraId) {
                const found = this.extrasSeleccionados.find(e => e.id === extraId);
                return found ? found.cantidad : 0;
            },

            obtenerStockBadge(v) {
                if (!this.productoSeleccionado) {
                    return { text: '', class: 'hidden' };
                }
                if (this.productoSeleccionado.controlar_stock === false || this.productoSeleccionado.controlar_stock == 0) {
                    return { text: '∞ Bajo Pedido', class: 'bg-neutral-100 text-neutral-600 border-neutral-200' };
                }
                const stock = parseInt(v.stock_fisico || 0) - parseInt(v.stock_reservado || 0);
                if (stock <= 0) {
                    return { text: 'Agotado (0)', class: 'bg-red-50 text-red-700 border-red-200' };
                }
                const min = parseInt(v.stock_minimo || 0) || 5;
                if (stock > min && stock > 5) {
                    return { text: 'Stock: ' + stock, class: 'bg-green-50 text-green-700 border-green-200' };
                }
                return { text: 'Stock: ' + stock, class: 'bg-amber-50 text-amber-700 border-amber-200' };
            },

            get totalEstimado() {
                let total = 0;
                if (this.varianteSeleccionada) {
                    total += parseFloat(this.varianteSeleccionada.precio);
                }
                total += this.extrasSeleccionados.reduce((s, e) => s + parseFloat(e.precio) * parseInt(e.cantidad), 0);
                return total;
            },

            confirmarSeleccion() {
                if (!this.varianteSeleccionada) return;

                const itemFormateado = {
                    index: this.indexDestino,
                    producto_variante_id: this.varianteSeleccionada.id,
                    sku: this.varianteSeleccionada.sku,
                    nombre_completo: this.varianteSeleccionada.nombre_completo,
                    precio_venta: this.totalEstimado,
                    costo_unitario: parseFloat(this.varianteSeleccionada.costo || 0) + this.extrasSeleccionados.reduce((s, e) => s + parseFloat(e.costo) * parseInt(e.cantidad), 0),
                    extras: this.extrasSeleccionados
                };

                this.$dispatch('producto-seleccionado', itemFormateado);

                this.cerrar();
            }
        };
    }
</script>
