@use(App\Support\Catalog)

<x-page title="Incidentes"
    :subtitle="auth()->user()->isAdmin() ? 'Novedades reportadas en los espacios.' : 'Novedades que has reportado.'">

    <x-slot:actions>
        <x-btn :href="route('incidents.create')">Reportar incidente</x-btn>
    </x-slot:actions>

    <form method="GET" action="{{ route('incidents.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="w-52">
            <x-select name="status" label="Estado" :options="Catalog::INCIDENT_STATUSES" :selected="request('status')"
                placeholder="Todos" />
        </div>
        <x-btn variant="secondary" type="submit">Filtrar</x-btn>
        @if (request()->filled('status'))
            <x-btn variant="secondary" :href="route('incidents.index')">Limpiar</x-btn>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 text-left text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Incidente</th>
                    <th class="px-4 py-3 font-medium">Espacio</th>
                    <th class="px-4 py-3 font-medium">Reportado</th>
                    <th class="px-4 py-3 font-medium">Prioridad</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 font-medium">Asignado a</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($incidents as $incident)
                    <tr class="text-zinc-700 dark:text-zinc-300">
                        <td class="px-4 py-3">
                            <a href="{{ route('incidents.show', $incident) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                                {{ $incident->title }}
                            </a>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">por {{ $incident->user->name }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $incident->reservation->space->name }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $incident->reported_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <x-badge :value="$incident->priority" :label="Catalog::label(Catalog::PRIORITIES, $incident->priority)" />
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :value="$incident->status" :label="Catalog::label(Catalog::INCIDENT_STATUSES, $incident->status)" />
                        </td>
                        <td class="px-4 py-3">{{ $incident->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-btn variant="secondary" :href="route('incidents.show', $incident)">Ver</x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            No hay incidentes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $incidents->links() }}
</x-page>
