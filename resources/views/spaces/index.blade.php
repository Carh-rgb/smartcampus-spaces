@use(App\Support\Catalog)

<x-page title="Espacios" subtitle="Salas, laboratorios, auditorios y demás espacios disponibles para reserva.">
    @can('create', App\Models\Space::class)
        <x-slot:actions>
            <x-btn :href="route('spaces.create')">Nuevo espacio</x-btn>
        </x-slot:actions>
    @endcan

    {{-- Filtros: se envian por GET para que la URL sea compartible --}}
    <form method="GET" action="{{ route('spaces.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="w-48">
            <x-select name="building" label="Bloque" :options="$buildings->mapWithKeys(fn ($b) => [$b => $b])->all()"
                :selected="request('building')" placeholder="Todos" />
        </div>
        <div class="w-48">
            <x-select name="status" label="Estado" :options="Catalog::SPACE_STATUSES" :selected="request('status')"
                placeholder="Todos" />
        </div>
        <x-btn variant="secondary" type="submit">Filtrar</x-btn>
        @if (request()->hasAny(['building', 'status']))
            <x-btn variant="secondary" :href="route('spaces.index')">Limpiar</x-btn>
        @endif
    </form>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 text-left text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Espacio</th>
                    <th class="px-4 py-3 font-medium">Bloque</th>
                    <th class="px-4 py-3 font-medium">Tipo</th>
                    <th class="px-4 py-3 text-right font-medium">Capacidad</th>
                    <th class="px-4 py-3 font-medium">Recursos</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($spaces as $space)
                    <tr class="text-zinc-700 dark:text-zinc-300">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                            <a href="{{ route('spaces.show', $space) }}" class="hover:underline" wire:navigate>{{ $space->name }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $space->building }}</td>
                        <td class="px-4 py-3">{{ Catalog::label(Catalog::SPACE_TYPES, $space->type) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $space->capacity }}</td>
                        <td class="px-4 py-3">{{ $space->resources_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :value="$space->status" :label="Catalog::label(Catalog::SPACE_STATUSES, $space->status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-btn variant="secondary" :href="route('spaces.show', $space)">Ver</x-btn>
                                @can('update', $space)
                                    <x-btn variant="secondary" :href="route('spaces.edit', $space)">Editar</x-btn>
                                    <form method="POST" action="{{ route('spaces.destroy', $space) }}"
                                        onsubmit="return confirm('¿Eliminar el espacio {{ $space->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-btn variant="danger" type="submit">Eliminar</x-btn>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            No hay espacios que coincidan con el filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $spaces->links() }}

    <p class="text-xs text-zinc-500 dark:text-zinc-400">
        {{ $spaces->total() }} espacio(s). Consulta en JSON: <code>GET /api/v1/spaces</code>
    </p>
</x-page>
