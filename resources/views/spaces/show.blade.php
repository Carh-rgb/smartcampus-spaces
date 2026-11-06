@use(App\Support\Catalog)

<x-page :title="$space->name" :subtitle="$space->building.' · '.Catalog::label(Catalog::SPACE_TYPES, $space->type).' · capacidad '.$space->capacity"
    :back="route('spaces.index')">

    <x-slot:actions>
        <x-badge :value="$space->status" :label="Catalog::label(Catalog::SPACE_STATUSES, $space->status)" />
        @can('update', $space)
            <x-btn variant="secondary" :href="route('spaces.edit', $space)">Editar</x-btn>
        @endcan
        <x-btn :href="route('reservations.create', ['space_id' => $space->id])">Reservar</x-btn>
    </x-slot:actions>

    @if ($space->description)
        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $space->description }}</p>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Relación N:M: recursos asignados con su cantidad (tabla pivote) --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Recursos del espacio</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Equipos instalados y su cantidad.</p>
            </div>

            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($space->resources as $resource)
                    <li class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 px-5 py-3 text-sm">
                        <div class="flex min-w-0 flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resource->name }}</span>
                            <x-badge :value="$resource->status" :label="Catalog::label(Catalog::RESOURCE_STATUSES, $resource->status)" />
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="tabular-nums text-zinc-600 dark:text-zinc-400">{{ $resource->pivot->quantity }} unidad(es)</span>
                            @can('manageResources', $space)
                                <form method="POST" action="{{ route('spaces.resources.destroy', [$space, $resource]) }}"
                                    onsubmit="return confirm('¿Retirar {{ $resource->name }} de este espacio?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">Retirar</button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        Este espacio todavía no tiene recursos asignados.
                    </li>
                @endforelse
            </ul>

            @if ($availableResources->isNotEmpty() && auth()->user()->can('manageResources', $space))
                <form method="POST" action="{{ route('spaces.resources.store', $space) }}"
                    class="flex flex-wrap items-end gap-3 border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                    @csrf
                    <div class="min-w-48 flex-1">
                        <x-select name="resource_id" label="Agregar recurso"
                            :options="$availableResources->pluck('name', 'id')->all()" placeholder="Selecciona..." required />
                    </div>
                    <div class="w-32">
                        <x-input name="quantity" label="Cantidad" type="number" value="1" min="1" max="500" required />
                    </div>
                    <x-btn variant="secondary" type="submit">Asignar</x-btn>
                </form>
            @endif
        </div>

        {{-- Relación 1:N: reservas del espacio --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Reservas registradas</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Solicitudes hechas sobre este espacio.</p>
            </div>

            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($space->reservations as $reservation)
                    <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $reservation->date->format('d/m/Y') }}
                                · {{ substr($reservation->start_time, 0, 5) }} a {{ substr($reservation->end_time, 0, 5) }}
                            </p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $reservation->user->name }} — {{ $reservation->purpose }}
                            </p>
                        </div>
                        <x-badge :value="$reservation->status" :label="Catalog::label(Catalog::RESERVATION_STATUSES, $reservation->status)" />
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        Este espacio no tiene reservas registradas.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</x-page>
