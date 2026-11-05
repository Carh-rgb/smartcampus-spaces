<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
    <div class="mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-6 py-16">
        {{-- Si la entidad define APP_ORGANIZATION en el .env, se muestra aquí --}}
        @if (config('app.organization'))
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                {{ config('app.organization') }}
            </p>
        @endif

        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">{{ config('app.name') }}</h1>

        <p class="mt-4 max-w-xl text-lg text-zinc-600 dark:text-zinc-400">
            Sistema de gestión y reserva de espacios físicos y recursos tecnológicos: solicita una sala,
            consulta su disponibilidad en tiempo real y reporta las novedades desde un solo lugar.
        </p>

        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="font-medium">Espacios y recursos</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Salas, laboratorios y auditorios con el equipamiento que tiene cada uno.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="font-medium">Reservas con aprobación</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Solicitudes por fecha y franja horaria, sin cruces de horario.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="font-medium">Incidentes con seguimiento</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Reporte de averías con responsable asignado y solución registrada.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="font-medium">Roles diferenciados</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Un administrador que aprueba y los solicitantes que piden el espacio.</p>
            </div>
        </div>

        <div class="mt-10 flex flex-wrap items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                    Ir al panel
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    Crear cuenta
                </a>
            @endauth
        </div>

        <p class="mt-12 text-xs text-zinc-500 dark:text-zinc-400">
            {{ config('app.name') }} · {{ now()->year }}
        </p>
    </div>
</body>

</html>
