<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<body class="bg-[#FAFAFA] font-sans text-neutral-900 antialiased min-h-screen flex items-center justify-center">

    <!-- Contenedor Central Minimalista (Estilo Linear / Vercel) -->
    <div class="w-full max-w-sm bg-white p-8 border border-neutral-100 rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
        
        <!-- Branding -->
        <div class="mb-8 text-center">
            <h1 class="text-xl font-bold tracking-tight text-neutral-900">Safa Digital</h1>
            <p class="text-sm text-neutral-500 mt-1">Ingresa con tus credenciales.</p>
        </div>

        <!-- Formulario Limpio -->
        <form method="POST" action="/login" class="space-y-5">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-neutral-900 mb-1.5">Correo electrónico</label>
                <input type="email" name="email" id="email" required autofocus
                    class="block w-full rounded-lg border border-neutral-200 px-3 py-2 text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 sm:text-sm transition-shadow">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-neutral-900">Contraseña</label>
                    <a href="#" class="text-xs font-medium text-neutral-500 hover:text-neutral-900 transition-colors">¿Olvidaste tu contraseña?</a>
                </div>
                <input type="password" name="password" id="password" required 
                    class="block w-full rounded-lg border border-neutral-200 px-3 py-2 text-neutral-900 placeholder-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-1 focus:ring-neutral-900 sm:text-sm transition-shadow">
            </div>

            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                <label for="remember_me" class="ml-2 block text-sm text-neutral-600">Mantener sesión iniciada</label>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-neutral-900 hover:bg-neutral-800 text-white font-medium py-2.5 rounded-lg transition-colors flex justify-center shadow-sm">
                    Iniciar Sesión
                </button>
            </div>
        </form>

    </div>

</body>
</html>
