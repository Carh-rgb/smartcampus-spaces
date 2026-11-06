<x-layouts.public>
    <div class="max-w-3xl">
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
            <x-feature-card title="Espacios y recursos">
                Salas, laboratorios y auditorios con el equipamiento que tiene cada uno.
            </x-feature-card>
            <x-feature-card title="Reservas con aprobación">
                Solicitudes por fecha y franja horaria, sin cruces de horario.
            </x-feature-card>
            <x-feature-card title="Incidentes con seguimiento">
                Reporte de averías con responsable asignado y solución registrada.
            </x-feature-card>
            <x-feature-card title="Roles diferenciados">
                Un administrador que aprueba y los solicitantes que piden el espacio.
            </x-feature-card>
        </div>

        <div class="mt-10 flex flex-wrap items-center gap-3">
            @auth
                <x-btn :href="route('dashboard')">Ir al panel</x-btn>
            @else
                <x-btn :href="route('login')">Iniciar sesión</x-btn>
                <x-btn variant="secondary" :href="route('register')">Crear cuenta</x-btn>
            @endauth
            <x-btn variant="secondary" :href="route('about')">Cómo funciona</x-btn>
        </div>
    </div>
</x-layouts.public>
