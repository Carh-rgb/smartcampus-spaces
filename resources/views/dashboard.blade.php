@use(App\Support\Catalog)

<x-page title="Panel"
    :subtitle="'Hola, '.auth()->user()->name.' · '.Catalog::label(Catalog::ROLES, auth()->user()->role)">

    <x-slot:actions>
        <x-btn :href="route('reservations.create')">Nueva reserva</x-btn>
    </x-slot:actions>

    {{-- Indicadores del estado actual --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('spaces.index') }}" class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500" wire:navigate>
            <p class="text-3xl font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $metrics['spaces'] }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">espacios disponibles</p>
        </a>
        <a href="{{ route('resources.index') }}" class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500" wire:navigate>
            <p class="text-3xl font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">{{ $metrics['resources'] }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">recursos en el catálogo</p>
        </a>
        <a href="{{ route('reservations.index', ['status' => 'pending']) }}" class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500" wire:navigate>
            <p class="text-3xl font-semibold tabular-nums text-amber-600 dark:text-amber-400">{{ $metrics['pending'] }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">reservas pendientes</p>
        </a>
        <a href="{{ route('incidents.index') }}" class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500" wire:navigate>
            <p class="text-3xl font-semibold tabular-nums text-red-600 dark:text-red-400">{{ $metrics['incidents'] }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">incidentes sin resolver</p>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ auth()->user()->isAdmin() ? 'Solicitudes por aprobar' : 'Tus próximas reservas' }}
                </h2>
                <a href="{{ route('reservations.index') }}" class="text-sm text-zinc-500 hover:underline dark:text-zinc-400" wire:navigate>Ver todas</a>
            </div>

            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($reservations as $reservation)
                    <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('reservations.show', $reservation) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                                {{ $reservation->space->name }}
                            </a>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $reservation->date->format('d/m/Y') }} ·
                                {{ substr($reservation->start_time, 0, 5) }} –
                                {{ substr($reservation->end_time, 0, 5) }} · {{ $reservation->user->name }}
                            </p>
                        </div>
                        <x-badge :value="$reservation->status" :label="Catalog::label(Catalog::RESERVATION_STATUSES, $reservation->status)" />
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Nada pendiente por ahora.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Incidentes abiertos</h2>
                <a href="{{ route('incidents.index') }}" class="text-sm text-zinc-500 hover:underline dark:text-zinc-400" wire:navigate>Ver todos</a>
            </div>

            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($incidents as $incident)
                    <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('incidents.show', $incident) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                                {{ $incident->title }}
                            </a>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $incident->reservation->space->name }}
                            </p>
                        </div>
                        <x-badge :value="$incident->priority" :label="Catalog::label(Catalog::PRIORITIES, $incident->priority)" />
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Sin incidentes abiertos.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-page>
