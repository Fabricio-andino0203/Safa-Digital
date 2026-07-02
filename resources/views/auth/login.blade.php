<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $favicon = get_setting('favicon_ruta'); @endphp
    @if($favicon && file_exists(public_path($favicon)))
        <link rel="icon" href="{{ asset($favicon) }}?v={{ time() }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <title>Safa Digital - Ingreso</title>
    <!-- Tailwind CSS CDN -->
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
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Tarjeta Central Premium (Estilo Notion / Linear) -->
    <div class="w-full max-w-sm bg-white p-8 rounded-2xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
        
        <!-- Branding y Logo -->
        <div class="mb-8 text-center">
            @php
                $logo_ruta = get_setting('logo_ruta');
                $clean_ruta = str_replace('storage/', '', $logo_ruta);
                $rutaAbsoluta = storage_path('app/public/' . $clean_ruta);
                $logoHtml = '';
                if ($logo_ruta && file_exists($rutaAbsoluta)) {
                    $ext = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
                    $data = file_get_contents($rutaAbsoluta);
                    $base64 = base64_encode($data);
                    $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . $ext;
                    $logoHtml = '<img src="data:' . $mime . ';base64,' . $base64 . '" style="height: 96px; max-height: none; width: auto; display: block; margin: 0 auto 12px auto; object-fit: contain;">';
                }
            @endphp
            
            @if($logoHtml)
                {!! $logoHtml !!}
            @else
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 mb-2">Safa Digital</h1>
            @endif
            
            <p class="text-sm text-neutral-500 mt-1">Ingresa tus credenciales para continuar</p>
        </div>

        <!-- Formulario Limpio -->
        <form method="POST" action="/login" class="space-y-4">
            @csrf
            
            <div>
                <label for="username" class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">Usuario</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus placeholder="admin"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                @error('username')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider">Contraseña</label>
                </div>
                <input type="password" name="password" id="password" required placeholder="••••••••"
                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-2.5 text-sm text-gray-800 shadow-sm transition-all focus:bg-white focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900">
                @error('password')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                    <span class="ml-2 text-xs text-neutral-500 font-medium">Mantener sesión</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-lg px-4 py-2.5 w-full transition-colors flex justify-center shadow-sm">
                    Iniciar Sesión
                </button>
            </div>
        </form>

    </div>

</body>
</html>
