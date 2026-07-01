<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido no encontrado - {{ get_setting('nombre_comercial', 'Safa Digital') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F9FAFB] text-neutral-800 antialiased min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-neutral-100 p-8 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-neutral-900 mb-2">Pedido no encontrado</h1>
        <p class="text-neutral-500 mb-8">El número de orden que buscas no existe o ha sido eliminado del sistema.</p>
        
        <div class="text-sm text-neutral-400">
            {{ get_setting('nombre_comercial', 'Safa Digital') }}
        </div>
    </div>

</body>
</html>
