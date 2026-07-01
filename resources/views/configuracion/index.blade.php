@extends('layouts.app')

@section('header_title', 'Configuración Global')

@section('content')
<div x-data="{ 
    tab: 'empresa', 
    modalUsuario: false, 
    modoEdicion: false, 
    userId: null,
    formUsuario: { name: '', username: '', password: '', rol: 'empleado', permisos: [] },
    abrirNuevoUsuario() {
        this.formUsuario = { name: '', username: '', password: '', rol: 'empleado', permisos: [] };
        this.userId = null;
        this.modoEdicion = false;
        this.modalUsuario = true;
    },
    abrirEditarUsuario(user) {
        this.formUsuario = { name: user.name, username: user.username, password: '', rol: user.rol, permisos: user.permisos || [] };
        this.userId = user.id;
        this.modoEdicion = true;
        this.modalUsuario = true;
    }
}" class="max-w-5xl mx-auto space-y-6">

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

    <div class="flex items-center gap-2 border-b border-neutral-200">
        <button @click="tab = 'empresa'" :class="tab === 'empresa' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'" class="px-5 py-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Empresa
        </button>
        <button @click="tab = 'tickets'" :class="tab === 'tickets' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'" class="px-5 py-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Tickets y PDFs
        </button>
        <button @click="tab = 'whatsapp'" :class="tab === 'whatsapp' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'" class="px-5 py-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            Mensajes WhatsApp
        </button>
        <button @click="tab = 'usuarios'" :class="tab === 'usuarios' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'" class="px-5 py-3 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Usuarios y Permisos
        </button>
    </div>

    <!-- Pestaña: Empresa -->
    <div x-show="tab === 'empresa'" x-cloak class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
        <form action="{{ route('configuracion.update.empresa') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 md:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-neutral-800 mb-2">Nombre Comercial</label>
                        <input type="text" name="nombre_comercial" value="{{ $configs['nombre_comercial'] ?? '' }}" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-neutral-50 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-neutral-800 mb-2">Teléfono Principal</label>
                        <input type="text" name="telefono" value="{{ $configs['telefono'] ?? '' }}" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-neutral-50 focus:bg-white transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-neutral-800 mb-2">Dirección Física</label>
                        <textarea name="direccion" rows="2" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-neutral-50 focus:bg-white transition-colors">{{ $configs['direccion'] ?? '' }}</textarea>
                    </div>
                </div>
                
                <div class="pt-6 border-t border-neutral-100">
                    <label class="block text-sm font-bold text-neutral-800 mb-4">Logo Oficial (Impresión y Tickets)</label>
                    <div class="flex items-center gap-6">
                        @if(isset($configs['logo_ruta']) && file_exists(public_path($configs['logo_ruta'])))
                            <div class="w-24 h-24 rounded-xl border border-neutral-200 bg-neutral-50 flex items-center justify-center p-2">
                                <img src="{{ asset($configs['logo_ruta']) }}?v={{ time() }}" alt="Logo" class="max-w-full max-h-full object-contain">
                            </div>
                        @else
                            <div class="w-24 h-24 rounded-xl border border-neutral-200 border-dashed bg-neutral-50 flex items-center justify-center p-2 text-neutral-400 text-xs text-center">
                                Sin Logo
                            </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/png, image/jpeg" class="block w-full text-sm text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-neutral-900 file:text-white hover:file:bg-neutral-800 cursor-pointer">
                            <p class="mt-2 text-xs text-neutral-500">Recomendado: PNG con fondo transparente. Máx 2MB.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-neutral-50 px-8 py-4 border-t border-neutral-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Pestaña: Tickets y PDFs -->
    <div x-show="tab === 'tickets'" x-cloak class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
        <form action="{{ route('configuracion.update.tickets') }}" method="POST">
            @csrf
            <div class="p-6 md:p-8 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-neutral-800 mb-2">Mensaje Pie de Página (Ticket 80mm)</label>
                    <p class="text-xs text-neutral-500 mb-3">Este mensaje aparecerá al final de todos los recibos impresos en caja.</p>
                    <textarea name="ticket_mensaje_pie" rows="3" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-neutral-50 focus:bg-white transition-colors">{{ $configs['ticket_mensaje_pie'] ?? '' }}</textarea>
                </div>
                <div class="pt-6 border-t border-neutral-100">
                    <label class="block text-sm font-bold text-neutral-800 mb-2">Términos y Condiciones (Orden A4)</label>
                    <p class="text-xs text-neutral-500 mb-3">Nota legal que aparece al final de la cotización o comprobante en tamaño carta.</p>
                    <textarea name="terminos_cotizacion" rows="4" required class="w-full rounded-xl border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-neutral-50 focus:bg-white transition-colors">{{ $configs['terminos_cotizacion'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="bg-neutral-50 px-8 py-4 border-t border-neutral-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Pestaña: WhatsApp -->
    <div x-show="tab === 'whatsapp'" x-cloak class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
        <form action="{{ route('configuracion.update.whatsapp') }}" method="POST">
            @csrf
            <div class="p-6 md:p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900">Plantillas de Mensajes</h3>
                    <p class="text-sm text-neutral-500 mt-1">
                        Utiliza estas variables en el texto: <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{cliente}</code>, <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{orden}</code>, <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{fecha_entrega}</code>, <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{saldo}</code>, <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{total}</code>, <code class="bg-neutral-100 px-1 py-0.5 rounded text-xs font-bold">{empresa}</code>.
                    </p>
                </div>

                <div class="space-y-6">
                    @foreach($plantillas as $p)
                    <div class="border border-neutral-200 rounded-xl p-5 bg-[#FAFAFA]">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="font-bold text-neutral-900">{{ $p->nombre }}</h4>
                                <span class="text-xs font-medium bg-neutral-200 text-neutral-600 px-2 py-0.5 rounded-md mt-1 inline-block">Trigger: {{ $p->evento }}</span>
                            </div>
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" name="plantillas[{{ $p->id }}][activa]" class="sr-only" {{ $p->activa ? 'checked' : '' }}>
                                    <div class="block bg-neutral-200 w-10 h-6 rounded-full transition-colors {{ $p->activa ? 'bg-green-500' : '' }}"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform {{ $p->activa ? 'transform translate-x-4' : '' }}"></div>
                                </div>
                                <span class="ml-3 text-sm font-bold text-neutral-900">{{ $p->activa ? 'Activa' : 'Inactiva' }}</span>
                            </label>
                        </div>
                        <textarea name="plantillas[{{ $p->id }}][contenido]" rows="4" class="w-full rounded-lg border border-neutral-200 px-4 py-3 text-sm focus:border-neutral-900 focus:outline-none bg-white transition-colors" placeholder="Escribe el mensaje aquí...">{{ $p->contenido }}</textarea>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-neutral-50 px-8 py-4 border-t border-neutral-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm">Guardar Plantillas</button>
            </div>
        </form>
    </div>

    <!-- Pestaña: Usuarios y Permisos -->
    <div x-show="tab === 'usuarios'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden p-6 md:p-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900">Usuarios del Sistema</h3>
                    <p class="text-sm text-neutral-500 mt-1">Administra las credenciales y los permisos granulares de tus empleados.</p>
                </div>
                <button @click="abrirNuevoUsuario()" class="px-5 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-xl hover:bg-neutral-800 transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo Usuario
                </button>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="border border-neutral-200 rounded-xl overflow-hidden bg-white">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50 border-b border-neutral-200 text-xs font-bold text-neutral-500 uppercase tracking-wider">
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4">Rol</th>
                            <th class="px-6 py-4">Permisos</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 text-sm text-neutral-700">
                        @foreach($usuarios as $user)
                        <tr class="hover:bg-neutral-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-neutral-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 font-mono">{{ $user->username }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $user->rol === 'admin' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-neutral-100 text-neutral-600' }}">
                                    {{ strtoupper($user->rol) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->id === 1)
                                    <span class="text-xs font-bold text-green-700 bg-green-50 border border-green-100 px-2 py-0.5 rounded">Acceso Total (SuperAdmin)</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->permisos ?? [] as $perm)
                                            <span class="text-xs bg-neutral-100 text-neutral-600 px-1.5 py-0.5 rounded border border-neutral-200/60 font-mono">{{ $perm }}</span>
                                        @empty
                                            <span class="text-xs text-neutral-400 italic">Ninguno</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="abrirEditarUsuario({{ json_encode($user) }})" class="text-neutral-500 hover:text-neutral-900 font-semibold text-xs">Editar</button>
                                @if($user->id !== 1)
                                    <form action="{{ route('configuracion.usuarios.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Crear/Editar Usuario -->
    <div x-show="modalUsuario" class="relative z-50" x-cloak>
        <div x-show="modalUsuario" x-transition.opacity class="fixed inset-0 bg-neutral-900/60 backdrop-blur-sm"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalUsuario"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     @click.away="modalUsuario = false"
                     class="relative w-full max-w-lg transform overflow-hidden rounded-3xl bg-white shadow-2xl p-7 border border-neutral-100 space-y-5">
                    
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                        <h3 class="text-lg font-bold text-neutral-900" x-text="modoEdicion ? 'Editar Usuario' : 'Registrar Nuevo Usuario'"></h3>
                        <button @click="modalUsuario = false" class="text-neutral-400 hover:text-neutral-700 bg-neutral-100 p-2 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="modoEdicion ? '/configuracion/usuarios/' + userId : '/configuracion/usuarios'" method="POST" class="space-y-4">
                        @csrf
                        <template x-if="modoEdicion">
                            <input type="hidden" name="_method" value="PATCH">
                        </template>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-1">Nombre Completo *</label>
                                <input type="text" name="name" x-model="formUsuario.name" required placeholder="Nombre..."
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-1">Usuario *</label>
                                <input type="text" name="username" x-model="formUsuario.username" required placeholder="ej. cajero1"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-1">Rol *</label>
                                <select name="rol" x-model="formUsuario.rol" required
                                        class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 cursor-pointer">
                                    <option value="empleado">Empleado</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-neutral-700 mb-1" x-text="modoEdicion ? 'Contraseña (Opcional)' : 'Contraseña *'"></label>
                                <input type="password" name="password" x-model="formUsuario.password" :required="!modoEdicion" placeholder="******"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                            </div>
                        </div>

                        <!-- Sección de Permisos checklist -->
                        <div class="pt-4 border-t border-neutral-100" x-show="userId !== 1">
                            <label class="block text-sm font-bold text-neutral-800 mb-3">Permisos de Módulo Granulares</label>
                            
                            <div class="grid grid-cols-2 gap-3 bg-neutral-50 rounded-2xl p-4 border border-neutral-100">
                                @php
                                    $modulosDisponibles = [
                                        'pedidos' => 'Pedidos (Kanban)',
                                        'pos' => 'Caja / POS (Ventas)',
                                        'clientes' => 'Clientes (Administración)',
                                        'cotizaciones' => 'Cotizaciones (Presupuestos)',
                                        'inventario' => 'Inventario (Productos/Stock)',
                                        'compras' => 'Compras (Proveedores/Stock)',
                                        'caja' => 'Tesorería (Movimientos Caja)',
                                        'configuracion' => 'Configuración (Ajustes/Usuarios)',
                                    ];
                                @endphp
                                @foreach($modulosDisponibles as $key => $label)
                                    <label class="flex items-center gap-3 cursor-pointer select-none">
                                        <input type="checkbox" name="permisos[]" value="{{ $key }}"
                                               :checked="formUsuario.permisos.includes('{{ $key }}')"
                                               @change="
                                                   if ($event.target.checked) {
                                                       if (!formUsuario.permisos.includes('{{ $key }}')) formUsuario.permisos.push('{{ $key }}');
                                                   } else {
                                                       formUsuario.permisos = formUsuario.permisos.filter(p => p !== '{{ $key }}');
                                                   }
                                               "
                                               class="w-4 h-4 rounded text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                                        <span class="text-xs font-semibold text-neutral-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end gap-3 border-t border-neutral-100">
                            <button type="button" @click="modalUsuario = false" class="px-5 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold rounded-xl text-sm transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm" x-text="modoEdicion ? 'Guardar Cambios' : 'Crear Usuario'"></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

<style>
    /* Toggle switch minimalista */
    input:checked ~ .dot {
        transform: translateX(100%);
    }
    input:checked ~ .block {
        background-color: #10B981; /* green-500 */
    }
</style>
@endsection
