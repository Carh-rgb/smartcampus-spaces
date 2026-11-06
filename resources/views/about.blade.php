{{-- Vista estatica: se sirve con Route::view('acerca', 'about') y no
     recibe datos de ningun controlador. --}}
<x-layouts.public title="Acerca de">
    <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Acerca del sistema</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Cómo funciona {{ config('app.name') }}</h1>

        <p class="mt-4 text-zinc-600 dark:text-zinc-400">
            {{ config('app.name') }} centraliza la solicitud y el control de los espacios físicos del campus.
            Evita que dos grupos ocupen la misma sala a la misma hora y deja registro de cada novedad
            que se presenta en los equipos.
        </p>

        <h2 class="mt-10 text-xl font-semibold">Flujo de una reserva</h2>
        <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Solicitar', 'El docente o estudiante elige el espacio, la fecha, la franja horaria y el motivo.'],
                ['Validar', 'El sistema rechaza la solicitud si se cruza con otra reserva activa.'],
                ['Aprobar', 'El administrador aprueba o rechaza la solicitud y deja una observación.'],
                ['Reportar', 'Si algo falla durante el uso, se reporta un incidente sobre esa reserva.'],
            ] as $i => [$paso, $detalle])
                <li class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <span class="flex size-7 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white dark:bg-zinc-100 dark:text-zinc-900">{{ $i + 1 }}</span>
                    <p class="mt-3 font-medium">{{ $paso }}</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $detalle }}</p>
                </li>
            @endforeach
        </ol>

        <h2 class="mt-10 text-xl font-semibold">Roles</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <x-feature-card title="Administrador">
                Gestiona espacios y recursos, aprueba o rechaza reservas y asigna la solución de los incidentes.
            </x-feature-card>
            <x-feature-card title="Docente y estudiante">
                Consultan los espacios, solicitan reservas, las cancelan si ya no las necesitan y reportan novedades.
            </x-feature-card>
        </div>

        <h2 class="mt-10 text-xl font-semibold">Consulta por API</h2>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            La disponibilidad de los espacios también se puede consultar en formato JSON, por ejemplo desde una app móvil:
        </p>
        <pre class="mt-3 overflow-x-auto rounded-xl bg-zinc-100 p-4 text-sm dark:bg-zinc-800"><code>GET /api/v1/spaces
GET /api/v1/spaces/{id}
GET /api/v1/spaces/{id}/availability?date=AAAA-MM-DD</code></pre>

        <div class="mt-10">
            <x-btn :href="route('home')" variant="secondary">&larr; Volver al inicio</x-btn>
        </div>
    </div>
</x-layouts.public>
