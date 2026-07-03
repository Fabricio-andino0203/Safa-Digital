<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favicon = get_setting('favicon_ruta'); @endphp
    @if($favicon && file_exists(public_path($favicon)))
        <link rel="icon" href="{{ asset($favicon) }}?v={{ time() }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <title>Safa Digital</title>
    <!-- Tailwind CSS CDN para renderizado rápido -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neutral: {
                            50: '#fafafa',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            500: '#737373',
                            900: '#171717',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#FAFAFA] text-neutral-900 antialiased font-sans flex h-screen overflow-hidden">

    <!-- Sidebar Izquierdo -->
    <aside class="w-64 bg-white border-r border-neutral-100 flex flex-col hidden md:flex flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-neutral-100 gap-2">
            @php $logo_ruta = get_setting('logo_ruta'); @endphp
            @if($logo_ruta && file_exists(public_path($logo_ruta)))
                <img src="{{ asset($logo_ruta) }}" alt="{{ get_setting('nombre_comercial', 'Safa Digital') }}" class="h-12 max-w-[200px] object-contain">
            @else
                <span class="text-xl font-bold tracking-tight">{{ get_setting('nombre_comercial', 'Safa Digital') }}</span>
            @endif
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'dashboard',        'label' => 'Inicio',     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'pedidos.index',    'label' => 'Pedidos',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['route' => 'pos.index',        'label' => 'Caja / POS', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['route' => 'caja.index',       'label' => 'Movimientos de Caja', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'clientes.index',   'label' => 'Clientes',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'cotizaciones.index','label' => 'Cotizaciones','icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'inventario.index', 'label' => 'Inventario', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['route' => 'compras.index',    'label' => 'Compras',    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                    ['route' => 'tesoreria.index',  'label' => 'Tesorería',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'reportes.index',   'label' => 'Reportes',   'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z'],
                    ['route' => 'configuracion.index','label' => 'Configuración','icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                    $modulo = explode('.', $item['route'])[0];
                    $hasPermission = true;
                    if (auth()->check() && $modulo !== 'dashboard') {
                        $permisoRequerido = ($modulo === 'tesoreria') ? 'caja' : $modulo;
                        $hasPermission = auth()->user()->tienePermiso($permisoRequerido);
                    }
                @endphp
                @if($hasPermission)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors
                              {{ $isActive
                                 ? 'bg-neutral-900 text-white shadow-sm'
                                 : 'text-neutral-500 hover:text-neutral-900 hover:bg-neutral-50' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"></path>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    <!-- Espacio Principal -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Header Superior -->
        <header class="h-16 flex items-center justify-between px-8 bg-white border-b border-neutral-100 flex-shrink-0">
            <h1 class="text-lg font-semibold text-neutral-900">@yield('header_title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <!-- Dropdown de Acceso Rápido -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-8 h-8 rounded-xl bg-orange-600 hover:bg-orange-700 text-white flex items-center justify-center transition-all shadow-sm active:scale-95 text-lg font-bold" title="Accesos Rápidos">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                    <!-- Menu Desplegable -->
                    <div x-show="open" 
                         @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-75" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95" 
                         class="absolute right-0 mt-2 w-72 bg-white border border-neutral-200 rounded-2xl shadow-xl p-4 z-50 space-y-3"
                         x-cloak>
                        <div class="text-xs font-bold text-neutral-400 uppercase tracking-wider px-1">Accesos Rápidos</div>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Crear Pedido -->
                            <a href="{{ route('pedidos.index') }}?crear=true" class="p-3 bg-neutral-50 hover:bg-orange-50 border border-neutral-100 hover:border-orange-200 rounded-xl flex flex-col items-center justify-center text-center transition-all group">
                                <span class="p-2 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-200 transition-colors mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                </span>
                                <span class="text-[11px] font-bold text-neutral-700">Crear Pedido</span>
                            </a>
                            <!-- Nueva Cotización -->
                            <a href="{{ route('cotizaciones.index') }}" class="p-3 bg-neutral-50 hover:bg-orange-50 border border-neutral-100 hover:border-orange-200 rounded-xl flex flex-col items-center justify-center text-center transition-all group">
                                <span class="p-2 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-200 transition-colors mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span class="text-[11px] font-bold text-neutral-700">Nueva Cotización</span>
                            </a>
                            <!-- Nueva Venta -->
                            <a href="{{ route('pos.index') }}" class="p-3 bg-neutral-50 hover:bg-orange-50 border border-neutral-100 hover:border-orange-200 rounded-xl flex flex-col items-center justify-center text-center transition-all group">
                                <span class="p-2 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-200 transition-colors mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </span>
                                <span class="text-[11px] font-bold text-neutral-700">Nueva Venta</span>
                            </a>
                            <!-- Nuevo Producto -->
                            <a href="{{ route('inventario.index') }}" class="p-3 bg-neutral-50 hover:bg-orange-50 border border-neutral-100 hover:border-orange-200 rounded-xl flex flex-col items-center justify-center text-center transition-all group">
                                <span class="p-2 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-200 transition-colors mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </span>
                                <span class="text-[11px] font-bold text-neutral-700">Nuevo Producto</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Dropdown de Notificaciones -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-500 hover:bg-neutral-200 transition-colors relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                        @endif
                    </button>
                    <!-- Menu Desplegable -->
                    <div x-show="open" 
                         @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-75" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95" 
                         class="absolute right-0 mt-2 w-80 bg-white border border-neutral-200 rounded-2xl shadow-xl p-4 z-50 space-y-3"
                         x-cloak>
                        <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                            <span class="text-xs font-bold text-neutral-500 uppercase tracking-wider">Notificaciones</span>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notificaciones.leerTodas') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 transition-colors">Leer todas</button>
                                </form>
                            @endif
                        </div>
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                @foreach(auth()->user()->unreadNotifications as $notification)
                                    <div class="p-3 bg-neutral-50 hover:bg-neutral-100 rounded-xl transition-all border border-neutral-100 text-left space-y-1 relative group">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-neutral-800">{{ $notification->data['titulo'] ?? 'Alerta' }}</span>
                                            <span class="text-[9px] text-neutral-400 font-medium">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[10px] text-neutral-500 leading-normal">{{ $notification->data['mensaje'] ?? '' }}</p>
                                        @if(!empty($notification->data['link']))
                                            <a href="{{ $notification->data['link'] }}" class="text-[9px] font-bold text-neutral-900 hover:underline block pt-1">Ver detalles &rarr;</a>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="py-6 text-center text-neutral-400 text-xs">
                                    Sin notificaciones pendientes.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center text-xs font-bold shadow-sm" title="{{ auth()->user()->name ?? 'SA' }}">
                        {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                    </div>
                    @if(auth()->check())
                        <form action="{{ route('logout') }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-neutral-500 hover:text-red-600 transition-colors flex items-center justify-center" title="Cerrar Sesión">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </header>
        
        <!-- Área Dinámica -->
        <div class="flex-1 overflow-x-auto overflow-y-auto">
            <div class="p-8 md:p-10 mx-auto max-w-screen-2xl h-full">
                @yield('content')
            </div>
        </div>
    </main>

    @stack('modals')
    @stack('scripts')
</body>
</html>
