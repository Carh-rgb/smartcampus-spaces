@use(App\Support\Catalog)

<x-page title="Reservas"
    :subtitle="auth()->user()->isAdmin() ? 'Todas las solicitudes registradas.' : 'Tus solicitudes de espacios.'">

    <x-slot:actions>
        <x-btn :href="route('reservations.create')">Nueva reserva</x-btn>
    </x-slot:actions>

    <form method="GET" action="{{ route('reservations.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="w-52">
            <x-select name="status" label="Estado" :options="Catalog::RESERVATION_STATUSES" :selected="request('status')"
                placeholder="Todos" />
        </div>
        <x-btn variant="secondary" type="submit">Filtrar</x-btn>
        @if (request()->filled('status'))
            <x-btn variant="secondary" :href="route('reservations.index')">Limpiar</x-btn>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 text-left text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Fecha</th>
                    <th class="px-4 py-3 font-medium">Horario</th>
                    <th class="px-4 py-3 font-medium">Espacio</th>
                    @if (auth()->user()->isAdmin())
                        <th class="px-4 py-3 font-medium">Solicitante</th>
                    @endif
                    <th class="px-4 py-3 font-medium">Motivo</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($reservations as $reservation)
                    <tr class="text-zinc-700 dark:text-zinc-300">
                        <td class="px-4 py-3 font-medium text-zinc-900 tabular-nums dark:text-zinc-100">
                            {{ $reservation->date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 tabular-nums">
                            {{ substr($reservation->start_time, 0, 5) }} – {{ substr($reservation->end_time, 0, 5) }}
                        </td>
                        <td class="px-4 py-3">{{ $reservation->space->name }}</td>
                        @if (auth()->user()->isAdmin())
                            <td class="px-4 py-3">{{ $reservation->user->name }}</td>
                        @endif
                        <td class="max-w-xs truncate px-4 py-3">{{ $reservation->purpose }}</td>
                        <td class="px-4 py-3">
                            <x-badge :value="$reservation->status" :label="Catalog::label(Catalog::RESERVATION_STATUSES, $reservation->status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-btn variant="secondary" :href="route('reservations.show', $reservation)">Ver</x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            No hay reservas que mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reservations->links() }}
</x-page>
