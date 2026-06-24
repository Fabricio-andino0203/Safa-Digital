<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</head>
<body class="bg-[#FAFAFA] text-neutral-900 antialiased font-sans flex h-screen overflow-hidden">

    <!-- Sidebar Izquierdo -->
    <aside class="w-64 bg-white border-r border-neutral-100 flex flex-col hidden md:flex flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-neutral-100">
            <span class="text-xl font-bold tracking-tight">Safa Digital</span>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'pedidos.index',    'label' => 'Pedidos',    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['route' => 'pos.index',        'label' => 'Caja / POS', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['route' => 'clientes.index',   'label' => 'Clientes',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'inventario.index', 'label' => 'Inventario', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['route' => 'caja.index',       'label' => 'Tesorería',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
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
            @endforeach
        </nav>
    </aside>

    <!-- Espacio Principal -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Header Superior -->
        <header class="h-16 flex items-center justify-between px-8 bg-white border-b border-neutral-100 flex-shrink-0">
            <h1 class="text-lg font-semibold text-neutral-900">@yield('header_title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <button class="w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-500 hover:bg-neutral-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </button>
                <div class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center text-sm font-medium shadow-sm">SA</div>
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
