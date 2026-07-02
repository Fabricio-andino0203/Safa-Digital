<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favicon = get_setting('favicon_ruta'); @endphp
    @if($favicon && file_exists(public_path($favicon)))
        <link class="favicon" rel="icon" href="{{ asset($favicon) }}?v={{ time() }}">
    @else
        <link class="favicon" rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <title>Caja POS — Safa Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neutral: {
                            50: '#fafafa', 100: '#f5f5f5', 200: '#e5e5e5',
                            300: '#d4d4d4', 400: '#a3a3a3', 500: '#737373',
                            600: '#525252', 700: '#404040', 800: '#262626', 900: '#171717',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #e5e5e5; border-radius: 99px; }
    </style>
</head>
<body class="bg-[#FAFAFA] text-neutral-900 antialiased font-sans h-screen overflow-hidden flex flex-col">

    {{-- Barra Superior del POS --}}
    <header class="h-14 bg-white border-b border-neutral-100 flex items-center justify-between px-6 flex-shrink-0 shadow-sm z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('pedidos.index') }}" class="flex items-center gap-2 text-neutral-400 hover:text-neutral-700 transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
            <div class="w-px h-5 bg-neutral-200"></div>
            <span class="text-base font-bold tracking-tight">Safa Digital</span>
            <span class="text-xs font-semibold bg-neutral-900 text-white px-2.5 py-1 rounded-lg">POS</span>
        </div>

        <div class="flex items-center gap-3">
            @yield('pos_header_actions')
            <div class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center text-xs font-bold">SA</div>
        </div>
    </header>

    {{-- Área Principal del POS --}}
    <main class="flex-1 overflow-hidden">
        @yield('pos_content')
    </main>

    @stack('pos_modals')
    @stack('pos_scripts')
</body>
</html>
