@extends('layouts.app')

@section('header_title', 'Cotizaciones Comerciales')

@section('content')
<div x-data="cotizacionesApp()" @producto-seleccionado.window="seleccionarProductoDesdeBuscadorGlobal($event.detail)" class="h-full flex flex-col">
    <!-- Header de Controles -->
    <div class="flex justify-between items-center mb-6 flex-shrink-0">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-neutral-900">Historial de Cotizaciones</h2>
            <div class="flex items-center gap-2 mt-3">
                <button @click="filtroEstado = 'todas'" :class="filtroEstado === 'todas' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Todas</button>
                <button @click="filtroEstado = 'Borrador'" :class="filtroEstado === 'Borrador' ? 'bg-neutral-600 text-white border-neutral-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Borrador</button>
                <button @click="filtroEstado = 'Enviada'" :class="filtroEstado === 'Enviada' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Enviada</button>
                <button @click="filtroEstado = 'Aceptada'" :class="filtroEstado === 'Aceptada' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Aceptada</button>
                <button @click="filtroEstado = 'Rechazada'" :class="filtroEstado === 'Rechazada' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-neutral-600 border border-neutral-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">Rechazada</button>
            </div>
        </div>
        <button @click="openCreateSlide()" class="px-5 py-3 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-all shadow-md active:scale-95 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Cotización
        </button>
    </div>

    <!-- Tabla Principal -->
    <div class="flex-1 bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm flex flex-col">
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-neutral-50 text-neutral-500 font-bold text-xs uppercase sticky top-0 z-10 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4">N° Cotización</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Fecha Emisión</th>
                        <th class="px-6 py-4">Validez</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Total</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <template x-for="c in cotizacionesFiltradas" :key="c.id">
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-neutral-900" x-text="c.numero_cotizacion"></td>
                            <td class="px-6 py-4 font-semibold text-neutral-800" x-text="c.cliente?.nombre || 'N/A'"></td>
                            <td class="px-6 py-4 text-neutral-500" x-text="formatFecha(c.fecha_emision)"></td>
                            <td class="px-6 py-4 text-neutral-500" x-text="c.validez_dias + ' días'"></td>
                            <td class="px-6 py-4">
                                <span :class="badgeClass(c.estado)" class="inline-block text-[10px] font-bold px-2 py-1 rounded-md uppercase border" x-text="c.estado"></span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-neutral-900" x-text="'L.' + Number(c.total).toFixed(2)"></td>
                            <td class="px-6 py-4 text-center">
                                <button @click="abrirDetalles(c)" class="p-1.5 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100 rounded-lg transition-colors" title="Ver Detalles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="cotizacionesFiltradas.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-neutral-400">
                            No se encontraron cotizaciones registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Nueva Cotización -->
    <div x-show="openSlideOver" class="fixed inset-0 z-50 overflow-hidden" x-cloak>
        <div x-show="openSlideOver" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 transition-opacity"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div x-show="openSlideOver"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
                 
                 <!-- Header -->
                 <div class="px-8 py-6 border-b border-neutral-100 flex items-center justify-between flex-shrink-0 bg-white">
                     <div>
                         <h3 class="text-lg font-bold text-neutral-900">Nueva Cotización</h3>
                         <p class="text-xs text-neutral-400 mt-1">Generar presupuesto para cliente sin afectar stock</p>
                     </div>
                     <button @click="openSlideOver = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-200 rounded-xl transition-all">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                     </button>
                 </div>

                 <!-- Body (Formulario) -->
                 <div class="flex-1 overflow-y-auto p-8 space-y-6">
                     <form class="space-y-6" @submit.prevent="submitCotizacion">
                         <!-- Cliente -->
                         <div class="relative" x-data="{ openMenu: false }">
                             <label class="block text-sm font-semibold text-neutral-700 mb-2">Cliente *</label>
                             <template x-if="clienteSeleccionadoObj">
                                 <div class="flex items-center justify-between p-3.5 bg-green-50 border border-green-200 rounded-xl text-green-800 font-medium">
                                     <span x-text="clienteSeleccionadoObj.nombre + (clienteSeleccionadoObj.telefono ? ' - ' + clienteSeleccionadoObj.telefono : '')"></span>
                                     <button type="button" @click="quitarCliente()" class="text-green-700 hover:text-green-900 font-bold p-1 bg-green-200 rounded-md">✕</button>
                                 </div>
                             </template>
                             <template x-if="!clienteSeleccionadoObj">
                                  <div class="flex gap-2">
                                      <input type="text" x-model="buscarClienteTerm" @input="openMenu = true" @focus="openMenu = true" @click.away="openMenu = false" placeholder="Buscar cliente por nombre..." class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                                      <button type="button" @click="modalQuickCliente = true; newClient.nombre = buscarClienteTerm" class="px-4 bg-neutral-100 text-neutral-700 hover:bg-neutral-200 transition-colors rounded-xl flex items-center justify-center" title="Crear Cliente Rápido">
                                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                      </button>
                                  </div>
                              </template>
                             
                             <!-- Menú Desplegable -->
                             <div x-show="openMenu && !clienteSeleccionadoObj && clientesFiltrados.length > 0" class="absolute left-0 right-0 mt-2 bg-white border border-neutral-200 rounded-2xl shadow-xl z-20 overflow-hidden max-h-60 overflow-y-auto">
                                 <template x-for="c in clientesFiltrados" :key="c.id">
                                     <button type="button" @click="seleccionarCliente(c); openMenu = false" class="w-full text-left px-4 py-3 hover:bg-neutral-50 border-b border-neutral-100 last:border-0 transition-colors">
                                         <span class="font-bold text-neutral-900" x-text="c.nombre"></span>
                                         <span class="text-xs text-neutral-400 block" x-text="c.telefono || 'Sin teléfono'"></span>
                                     </button>
                                 </template>
                             </div>
                         </div>

                         <!-- Validez -->
                         <div>
                             <label class="block text-sm font-semibold text-neutral-700 mb-2">Días de Validez *</label>
                             <input type="number" x-model.number="form.validez_dias" min="1" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors">
                         </div>

                         <!-- Ítems / Detalles -->
                         <div class="border-t border-neutral-100 pt-6">
                             <div class="flex justify-between items-center mb-4">
                                 <h4 class="text-sm font-bold text-neutral-900 uppercase tracking-wider">Productos / Conceptos</h4>
                                 <button type="button" @click="agregarItem()" class="text-sm font-semibold text-blue-600 hover:text-blue-700">+ Agregar Ítem</button>
                             </div>

                             <div class="space-y-4">
                                 <template x-for="(item, index) in form.detalles" :key="index">
                                     <div class="p-5 border border-neutral-200 rounded-2xl bg-neutral-50/50 space-y-4 relative group">
                                         <button type="button" @click="form.detalles.splice(index, 1)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold shadow-sm">✕</button>

                                         <div class="grid grid-cols-12 gap-4">
                                             <!-- Tipo -->
                                             <div class="col-span-12 md:col-span-3">
                                                 <label class="block text-xs font-semibold text-neutral-500 mb-1">Tipo</label>
                                                 <select x-model="item.tipo_producto" @change="cambiarTipoItem(index)" class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white">
                                                     <option value="Inventario">Inventario</option>
                                                     <option value="Libre">Libre</option>
                                                 </select>
                                             </div>

                                             <!-- Selector Inventario o Inputs Libre -->
                                             <div class="col-span-12 md:col-span-9">
                                                 <!-- Inventario -->
                                                 <template x-if="item.tipo_producto === 'Inventario'">
                                                     <div>
                                                         <label class="block text-xs font-semibold text-neutral-500 mb-1">Producto / Variante *</label>
                                                          <button type="button" @click="$dispatch('abrir-buscador-global', { index: index })"
                                                                  class="w-full text-left rounded-xl border border-neutral-200 px-3 py-2 text-sm focus:outline-none bg-white hover:bg-neutral-50 transition-colors flex items-center justify-between">
                                                              <span x-text="item.producto_variante_id ? (variantesList.find(v => v.id == item.producto_variante_id)?.sku + ' - ' + variantesList.find(v => v.id == item.producto_variante_id)?.nombre_completo) : '🔍 Buscar Producto en Inventario'"
                                                                    :class="item.producto_variante_id ? 'text-neutral-900 font-bold' : 'text-neutral-400 font-semibold'"></span>
                                                              <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                                                          </button>
                                                          <input type="hidden" x-model="item.producto_variante_id" required />

                                                          <!-- Contenedor de Extras Seleccionados en Cotizaciones -->
                                                          <template x-if="item.producto_variante_id && item.extras && item.extras.length > 0">
                                                              <div class="mt-2 space-y-1 bg-white border border-neutral-200/80 rounded-xl p-2.5">
                                                                  <span class="text-[9px] font-bold text-neutral-400 uppercase tracking-wider block">Extras Seleccionados:</span>
                                                                  <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                                      <template x-for="ex in item.extras" :key="ex.id">
                                                                          <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-neutral-50 border border-neutral-200 rounded-lg text-[10px] font-semibold text-neutral-700 shadow-sm">
                                                                              <span x-text="ex.cantidad + 'x ' + ex.nombre + ' (+L. ' + Number(ex.precio * ex.cantidad).toFixed(2) + ')'"></span>
                                                                          </span>
                                                                      </template>
                                                                  </div>
                                                              </div>
                                                          </template>
                                                     </div>
                                                 </template>
                                                 <!-- Libre -->
                                                 <template x-if="item.tipo_producto === 'Libre'">
                                                     <div class="grid grid-cols-2 gap-3">
                                                         <div>
                                                             <label class="block text-xs font-semibold text-neutral-500 mb-1">Descripción Ítem *</label>
                                                             <input type="text" x-model="item.nombre_libre" placeholder="Ej. Lona Impresa" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white">
                                                         </div>
                                                         <div>
                                                             <label class="block text-xs font-semibold text-neutral-500 mb-1">Detalle opcional</label>
                                                             <input type="text" x-model="item.descripcion_libre" placeholder="Medida, color, etc." class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white">
                                                         </div>
                                                     </div>
                                                 </template>
                                             </div>
                                         </div>

                                         <!-- Valores de Venta / Costo / Cantidad -->
                                         <div class="grid grid-cols-12 gap-4">
                                            @if(auth()->id() === 1 || auth()->user()->rol === 'administrador')
                                              <!-- Costo (Requerido para libres y mostrado para inventario) -->
                                              <div class="col-span-12 md:col-span-4">
                                                  <label class="block text-xs font-semibold text-neutral-500 mb-1">Costo Unit. (L.) *</label>
                                                  <input type="number" step="0.01" min="0" x-model.number="item.costo_libre" :disabled="item.tipo_producto === 'Inventario'" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white text-right disabled:bg-neutral-100 disabled:text-neutral-500">
                                              </div>
                                            @else
                                              <input type="hidden" x-model.number="item.costo_libre">
                                            @endif
                                             <!-- Venta -->
                                             <div class="col-span-12 md:col-span-4">
                                                 <label class="block text-xs font-semibold text-neutral-500 mb-1">Precio Venta (L.) *</label>
                                                 <input type="number" step="0.01" min="0" x-model.number="item.precio_venta" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white text-right">
                                             </div>
                                             <!-- Cantidad -->
                                             <div class="col-span-12 md:col-span-4">
                                                 <label class="block text-xs font-semibold text-neutral-500 mb-1">Cant. *</label>
                                                 <input type="number" min="1" x-model.number="item.cantidad" required class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm focus:border-neutral-900 focus:outline-none bg-white text-center">
                                             </div>
                                         </div>

                                         <div class="flex justify-between items-center text-xs font-medium text-neutral-500 pt-2 border-t border-neutral-100">
                                             @if(auth()->id() === 1 || auth()->user()->rol === 'administrador')
                                             <div>
                                                 <span>Margen Ganancia: </span>
                                                 <span class="font-bold" :class="(item.precio_venta - item.costo_libre) >= 0 ? 'text-green-600' : 'text-red-500'" x-text="'L.' + ((item.precio_venta - item.costo_libre) * item.cantidad).toFixed(2)"></span>
                                             </div>
                                             @else
                                             <div></div>
                                             @endif
                                             <div class="text-sm font-bold text-neutral-900">
                                                 Subtotal: L.<span x-text="(item.precio_venta * item.cantidad).toFixed(2)"></span>
                                             </div>
                                         </div>
                                     </div>
                                 </template>
                             </div>
                         </div>

                         <!-- Notas -->
                         <div>
                             <label class="block text-sm font-semibold text-neutral-700 mb-2">Notas / Condiciones de Validez</label>
                             <textarea x-model="form.notas" rows="2" placeholder="Notas sobre el presupuesto..." class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-[#FAFAFA] focus:bg-white transition-colors"></textarea>
                         </div>

                         <!-- Resumen Financiero -->
                         <div class="bg-neutral-900 p-6 rounded-3xl text-white space-y-4 shadow-lg">
                             <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-400 mb-2">Resumen</h3>
                             <div class="flex justify-between items-center">
                                 <span class="text-sm text-neutral-300">Subtotal</span>
                                 <span class="text-sm font-medium" x-text="'L.' + calculoSubtotal.toFixed(2)"></span>
                             </div>
                             <div class="flex justify-between items-center">
                                 <span class="text-sm text-neutral-300">Descuento Global (L.)</span>
                                 <input type="number" step="0.01" x-model.number="form.descuento" class="w-24 text-right rounded-lg bg-neutral-800 border border-neutral-700 px-3 py-1.5 text-sm focus:border-white focus:outline-none transition-colors">
                             </div>
                             <div class="pt-4 border-t border-neutral-800 flex justify-between items-center">
                                 <span class="text-base font-bold">Total Presupuestado</span>
                                 <span class="text-2xl font-bold" x-text="'L.' + calculoTotal.toFixed(2)"></span>
                             </div>
                         </div>

                         <div x-show="errorMensaje" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm font-medium text-red-600" x-text="errorMensaje"></div>
                     </form>
                 </div>

                 <!-- Footer -->
                 <div class="px-8 py-5 border-t border-neutral-100 bg-white flex justify-end gap-3 flex-shrink-0">
                     <button @click="openSlideOver = false" class="px-6 py-3 text-sm font-bold text-neutral-500 hover:bg-neutral-100 rounded-xl transition-colors">Cancelar</button>
                     <button @click="submitCotizacion()" :disabled="guardando || form.detalles.length === 0" class="px-8 py-3 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 active:scale-95 transition-all shadow-md disabled:opacity-50 flex items-center gap-2">
                         <svg x-show="guardando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                         <span x-text="guardando ? 'Guardando...' : 'Crear Cotización'"></span>
                     </button>
                 </div>
            </div>
        </div>
    </div>

    <!-- Modal: Detalle de Cotización -->
    <div x-show="modalDetalles" class="relative z-50" x-cloak>
        <div x-show="modalDetalles" x-transition.opacity class="fixed inset-0 bg-neutral-900/40 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalDetalles"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-4xl transform overflow-hidden rounded-3xl bg-white shadow-2xl">
                    
                     <template x-if="cotizacionSeleccionada">
                        <div>
                            <!-- Header -->
                            <div class="px-8 py-5 border-b border-neutral-100 flex items-center justify-between bg-white">
                                <div>
                                    <h3 class="text-lg font-bold text-neutral-900">Resumen del Presupuesto</h3>
                                </div>
                                <button @click="modalDetalles = false" class="w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 rounded-xl transition-all">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="p-8 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="md:col-span-2 space-y-4">
                                        <div class="bg-[#FAFAFA] border border-neutral-200 rounded-2xl p-5 shadow-sm">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <span class="text-xs font-bold text-neutral-900 bg-neutral-200 px-2.5 py-1 rounded-md" x-text="cotizacionSeleccionada.numero_cotizacion"></span>
                                                    <h4 class="text-xl font-bold text-neutral-900 mt-2" x-text="cotizacionSeleccionada.cliente?.nombre"></h4>
                                                    <p class="text-xs text-neutral-500 mt-1" x-text="cotizacionSeleccionada.cliente?.telefono ? 'Teléfono: ' + cotizacionSeleccionada.cliente.telefono : ''"></p>
                                                </div>
                                                <div class="text-right">
                                                    <span :class="badgeClass(cotizacionSeleccionada.estado)" class="inline-block text-[10px] font-bold px-2 py-1 rounded-md uppercase border" x-text="cotizacionSeleccionada.estado"></span>
                                                    <p class="text-xs text-neutral-400 mt-1">Validez: <span class="font-bold text-neutral-900" x-text="cotizacionSeleccionada.validez_dias + ' días'"></span></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tabla de detalles -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm">
                                            <table class="w-full text-left text-sm border-collapse">
                                                <thead class="bg-neutral-50 text-neutral-500 font-bold text-xs uppercase border-b border-neutral-100">
                                                    <tr>
                                                        <th class="px-4 py-3">Ítem</th>
                                                        <th class="px-4 py-3 text-center">Cant.</th>
                                                        <th class="px-4 py-3 text-right">Precio</th>
                                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-neutral-100">
                                                    <template x-for="d in cotizacionSeleccionada.detalles" :key="d.id">
                                                        <tr>
                                                            <td class="px-4 py-3 font-bold text-neutral-900">
                                                                <span x-text="d.tipo_producto === 'Inventario' ? d.variante?.producto?.nombre : d.nombre_libre"></span>
                                                                <span x-show="d.tipo_producto === 'Inventario'" class="text-[10px] text-neutral-400 block font-normal" x-text="'SKU: ' + d.variante?.sku"></span>
                                                                <span x-show="d.tipo_producto === 'Libre' && d.descripcion_libre" class="text-[10px] text-neutral-400 block font-normal" x-text="d.descripcion_libre"></span>
                                                            </td>
                                                            <td class="px-4 py-3 text-center font-bold text-neutral-600" x-text="d.cantidad"></td>
                                                            <td class="px-4 py-3 text-right text-neutral-500" x-text="'L.' + Number(d.precio_venta).toFixed(2)"></td>
                                                            <td class="px-4 py-3 text-right font-bold text-neutral-900" x-text="'L.' + Number(d.subtotal).toFixed(2)"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Columna Rentabilidad & Acciones -->
                                    <div class="space-y-4">
                                        <!-- Rentabilidad / Margen -->
                                        <div class="bg-neutral-900 p-6 rounded-2xl text-white shadow-lg space-y-4">
                                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Métricas de Cotización</h4>
                                            <div class="space-y-2">
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-neutral-400">Total Venta</span>
                                                    <span class="font-bold text-neutral-200" x-text="'L.' + Number(cotizacionSeleccionada.total).toFixed(2)"></span>
                                                </div>
                                                @if(auth()->id() === 1 || auth()->user()->rol === 'administrador')
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-neutral-400">Costo Estimado</span>
                                                    <span class="font-bold text-neutral-200" x-text="'L.' + obtenerCostoTotal(cotizacionSeleccionada).toFixed(2)"></span>
                                                </div>
                                                <div class="pt-2 border-t border-neutral-800 flex justify-between items-end">
                                                    <span class="text-xs font-bold text-neutral-400 uppercase">Margen Real</span>
                                                    <span class="text-2xl font-black text-green-400" x-text="'L.' + (cotizacionSeleccionada.total - obtenerCostoTotal(cotizacionSeleccionada)).toFixed(2)"></span>
                                                </div>
                                                <div class="text-[10px] text-right text-green-300 font-bold" x-text="'Margin %: ' + obtenerMargenPorcentaje(cotizacionSeleccionada) + '%'"></div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Botones de Acción -->
                                        <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm space-y-2 flex flex-col">
                                            <a :href="'/cotizaciones/' + cotizacionSeleccionada.id + '/pdf'" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border border-neutral-200 text-neutral-700 text-sm font-bold rounded-xl hover:bg-neutral-50 transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Descargar PDF (A4)
                                            </a>
                                            <a :href="obtenerWhatsappUrl(cotizacionSeleccionada)" target="_blank" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm font-bold rounded-xl hover:bg-green-100 transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.384 0 12.032c0 2.115.548 4.184 1.591 6.002L.039 24l6.113-1.605A11.968 11.968 0 0012.031 24c6.645 0 12.03-5.384 12.03-12.032C24.061 5.384 18.676 0 12.031 0zm3.87 17.262c-.22.617-1.282 1.157-1.802 1.258-.521.1-1.218.176-2.923-.523-2.035-.833-3.327-2.936-3.427-3.072-.101-.137-2.183-2.909-2.183-5.551 0-2.641 1.365-3.935 1.865-4.471.5-.536 1.08-.67 1.441-.67.36 0 .72.015.98.027.28.013.66.082 1.03.972.37.89 1.26 3.09 1.37 3.32.11.23.18.5.05.77-.13.27-.2.43-.4.67-.2.23-.42.52-.6.71-.2.2-.41.42-.18.82.23.4 1.02 1.69 2.19 2.73 1.5 1.35 2.74 1.77 3.14 1.93.4.17.64.13.88-.13.24-.27 1.02-1.19 1.3-1.6.28-.4.56-.34.93-.2.37.13 2.33 1.1 2.73 1.3.4.2.66.3.76.47.1.17.1.99-.12 1.6z"/></svg>
                                                Enviar por WhatsApp
                                            </a>
                                            
                                            <template x-if="cotizacionSeleccionada.pedido_id">
                                                <a :href="'/pedidos?id=' + cotizacionSeleccionada.pedido_id" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm text-center">
                                                    Ir al Pedido Generado (<span x-text="cotizacionSeleccionada.pedido?.numero_orden || '#ORD-' + cotizacionSeleccionada.pedido_id"></span>)
                                                </a>
                                            </template>
                                            
                                            <template x-if="!cotizacionSeleccionada.pedido_id">
                                                <div class="w-full space-y-2 flex flex-col">
                                                    <template x-if="cotizacionSeleccionada.estado !== 'Aceptada'">
                                                        <button @click="cambiarEstadoCotizacion(cotizacionSeleccionada, 'Aceptada')" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-neutral-900 hover:bg-neutral-800 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                                                            ✓ Marcar como Aceptada
                                                        </button>
                                                    </template>
                                                    <template x-if="cotizacionSeleccionada.estado === 'Aceptada'">
                                                        <div class="p-3 bg-green-50 text-green-700 text-xs font-bold text-center rounded-xl border border-green-200">
                                                            ✓ Aceptada por el Cliente
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast de Error Elegante -->
    <div x-show="toastError" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed bottom-6 right-6 z-50 bg-red-600 text-white px-5 py-4 rounded-2xl shadow-2xl flex items-center justify-between gap-4 max-w-md border border-red-500/30"
         style="display: none;">
        <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-red-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm">Error de Proceso</h4>
                <p class="text-xs text-red-100 mt-1" x-text="toastError"></p>
            </div>
        </div>
        <button @click="toastError = ''" class="text-red-200 hover:text-white font-bold text-sm p-1 ml-2">✕</button>
    </div>
    <!-- Modal: Nuevo Cliente Rápido -->
    <div x-show="modalQuickCliente" class="relative z-[60]" x-cloak>
        <div x-show="modalQuickCliente" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalQuickCliente"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalQuickCliente = false"
                     class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <h3 class="text-lg font-bold text-neutral-900">Nuevo Cliente</h3>
                        <button @click="modalQuickCliente = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-if="quickClientError">
                            <p class="text-xs text-red-500" x-text="quickClientError"></p>
                        </template>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Nombre Completo *</label>
                            <input type="text" x-model="newClient.nombre" required placeholder="Nombre..."
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Teléfono</label>
                            <input type="text" x-model="newClient.telefono" placeholder="Ej. 9988-7766"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Email</label>
                            <input type="email" x-model="newClient.email" placeholder="correo@ejemplo.com"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalQuickCliente = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="button" @click="guardarQuickCliente()" :disabled="guardandoQuickClient" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm disabled:opacity-50">
                                <span x-show="!guardandoQuickClient">Guardar Cliente</span>
                                <span x-show="guardandoQuickClient">Guardando...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <!-- Componente Global de Búsqueda de Inventario (Tarea 1) -->
    <x-buscador-inventario-global />

</div>
@endsection

@push('scripts')
<script>
    function cotizacionesApp() {
        return {
            filtroEstado: 'todas',
            openSlideOver: false,
            modalDetalles: false,
            modalQuickCliente: false,
            newClient: { nombre: '', telefono: '', email: '' },
            guardandoQuickClient: false,
            quickClientError: '',
            
            async guardarQuickCliente() {
                if (!this.newClient.nombre.trim()) {
                    this.quickClientError = 'El nombre es obligatorio.';
                    return;
                }
                this.guardandoQuickClient = true;
                this.quickClientError = '';

                try {
                    const res = await fetch('{{ route('clientes.quickStore') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newClient)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.clientesList.push(data.cliente);
                        this.seleccionarCliente(data.cliente);
                        this.modalQuickCliente = false;
                        this.newClient = { nombre: '', telefono: '', email: '' };
                    } else {
                        this.quickClientError = data.message || 'Error al guardar.';
                    }
                } catch (e) {
                    this.quickClientError = 'Error de conexión.';
                } finally {
                    this.guardandoQuickClient = false;
                }
            },
            cotizacionSeleccionada: null,
            guardando: false,
            errorMensaje: '',
            toastError: '',
            
            showToast(msg) {
                this.toastError = msg;
                setTimeout(() => {
                    if (this.toastError === msg) this.toastError = '';
                }, 5000);
            },

            seleccionarProductoDesdeBuscadorGlobal(detail) {
                const index = detail.index;
                if (this.form.detalles[index]) {
                    this.form.detalles[index].producto_variante_id = detail.producto_variante_id;
                    this.form.detalles[index].precio_venta = detail.precio_venta;
                    this.form.detalles[index].costo_libre = detail.costo_unitario;
                    this.form.detalles[index].extras = detail.extras;
                }
            },
            
            // Listas inyectadas
            clientesList: @json($clientes),
            variantesList: @json($variantes),
            cotizacionesList: @json($cotizaciones),

            // Formulario
            buscarClienteTerm: '',
            clienteSeleccionadoObj: null,
            form: {
                cliente_id: '',
                validez_dias: 15,
                descuento: 0,
                notas: '',
                detalles: []
            },

            get clientesFiltrados() {
                if(!this.buscarClienteTerm) return this.clientesList.slice(0, 5);
                const term = this.buscarClienteTerm.toLowerCase();
                return this.clientesList.filter(c => c.nombre.toLowerCase().includes(term));
            },

            get cotizacionesFiltradas() {
                if(this.filtroEstado === 'todas') return this.cotizacionesList;
                return this.cotizacionesList.filter(c => c.estado === this.filtroEstado);
            },

            get calculoSubtotal() {
                return this.form.detalles.reduce((acc, item) => acc + (item.precio_venta * item.cantidad), 0);
            },

            get calculoTotal() {
                return Math.max(0, this.calculoSubtotal - (Number(this.form.descuento) || 0));
            },

            seleccionarCliente(c) {
                this.form.cliente_id = c.id;
                this.clienteSeleccionadoObj = c;
                this.buscarClienteTerm = '';
            },

            quitarCliente() {
                this.form.cliente_id = '';
                this.clienteSeleccionadoObj = null;
            },

            openCreateSlide() {
                this.errorMensaje = '';
                this.form = {
                    cliente_id: '',
                    validez_dias: 15,
                    descuento: 0,
                    notas: '',
                    detalles: [
                        { tipo_producto: 'Inventario', producto_variante_id: '', nombre_libre: '', descripcion_libre: '', costo_libre: 0, precio_venta: 0, cantidad: 1 }
                    ]
                };
                this.clienteSeleccionadoObj = null;
                this.buscarClienteTerm = '';
                this.openSlideOver = true;
            },

            agregarItem() {
                this.form.detalles.push({
                    tipo_producto: 'Inventario',
                    producto_variante_id: '',
                    nombre_libre: '',
                    descripcion_libre: '',
                    costo_libre: 0,
                    precio_venta: 0,
                    cantidad: 1
                });
            },

            cambiarTipoItem(index) {
                const item = this.form.detalles[index];
                if (item.tipo_producto === 'Inventario') {
                    item.nombre_libre = '';
                    item.descripcion_libre = '';
                    item.costo_libre = 0;
                    item.precio_venta = 0;
                } else {
                    item.producto_variante_id = '';
                    item.costo_libre = 0;
                    item.precio_venta = 0;
                }
            },

            cargarPrecioInventario(index) {
                const item = this.form.detalles[index];
                const variant = this.variantesList.find(v => v.id == item.producto_variante_id);
                if (variant) {
                    item.precio_venta = variant.precio;
                    item.costo_libre = variant.costo;
                }
            },

            async submitCotizacion() {
                if(!this.form.cliente_id || this.form.detalles.length === 0) {
                    this.errorMensaje = 'El cliente y al menos un ítem son requeridos.';
                    return;
                }
                
                this.guardando = true;
                this.errorMensaje = '';

                try {
                    const res = await fetch('/cotizaciones', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await res.json();
                    if(res.ok && data.success) {
                        this.openSlideOver = false;
                        if(data.pdf_url) {
                            window.open(data.pdf_url, '_blank');
                        }
                        window.location.reload();
                    } else {
                        this.errorMensaje = data.message || 'Error al guardar la cotización.';
                    }
                } catch(e) {
                    this.errorMensaje = 'Error de red al guardar.';
                } finally {
                    this.guardando = false;
                }
            },

            abrirDetalles(cotizacion) {
                this.cotizacionSeleccionada = JSON.parse(JSON.stringify(cotizacion));
                this.modalDetalles = true;
            },

            obtenerCostoTotal(cotizacion) {
                if (!cotizacion.detalles) return 0;
                return cotizacion.detalles.reduce((acc, d) => {
                    let cost = d.tipo_producto === 'Inventario' ? Number(d.variante?.costo || 0) : Number(d.costo_libre || 0);
                    return acc + (cost * Number(d.cantidad));
                }, 0);
            },

            obtenerMargenPorcentaje(cotizacion) {
                const total = Number(cotizacion.total);
                if(total <= 0) return '0.00';
                const cost = this.obtenerCostoTotal(cotizacion);
                const profit = total - cost;
                return ((profit / total) * 100).toFixed(2);
            },

            obtenerWhatsappUrl(cotizacion) {
                if(!cotizacion.cliente) return '#';
                const text = `Hola ${cotizacion.cliente.nombre}, adjunto el enlace a tu cotización ${cotizacion.numero_cotizacion} por un total de L.${Number(cotizacion.total).toFixed(2)}. Puedes verla en el siguiente enlace: ${encodeURIComponent(window.location.origin + '/cotizaciones/' + cotizacion.id + '/pdf')}`;
                const tel = cotizacion.cliente.telefono ? cotizacion.cliente.telefono.replace(/\D/g, '') : '';
                return `https://wa.me/${tel}?text=${text}`;
            },

            async cambiarEstadoCotizacion(cotizacion, nuevoEstado) {
                try {
                    const res = await fetch(`/cotizaciones/${cotizacion.id}/estado`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ estado: nuevoEstado })
                    });
                    const data = await res.json();
                    if(res.ok && data.success) {
                        cotizacion.estado = nuevoEstado;
                        window.location.reload();
                    } else {
                        this.showToast(data.message || 'Error al cambiar estado.');
                    }
                } catch(e) {
                    this.showToast('Error de conexión.');
                }
            },

            formatFecha(dateStr) {
                if(!dateStr) return '';
                const parts = dateStr.substring(0, 10).split('-');
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            },

            badgeClass(estado) {
                return match = {
                    'Borrador': 'bg-neutral-50 text-neutral-600 border-neutral-200',
                    'Enviada': 'bg-blue-50 text-blue-700 border-blue-100',
                    'Aceptada': 'bg-green-50 text-green-700 border-green-100',
                    'Rechazada': 'bg-red-50 text-red-700 border-red-100',
                }[estado] || 'bg-neutral-50 text-neutral-500 border-neutral-100';
            }
        }
    }
</script>
@endpush
