@use(App\Support\Catalog)

<x-page title="Catálogo de recursos" subtitle="Equipamiento tecnológico que puede estar instalado en cada espacio.">
    @if (auth()->user()->isAdmin())
        <x-slot:actions>
            <x-btn :href="route('resources.create')">Nuevo recurso</x-btn>
        </x-slot:actions>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 text-left text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Recurso</th>
                    <th class="px-4 py-3 font-medium">Descripción</th>
                    <th class="px-4 py-3 text-right font-medium">Espacios</th>
                    <th class="px-4 py-3 font-medium">Estado</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($resources as $resource)
                    <tr class="text-zinc-700 dark:text-zinc-300">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                            <a href="{{ route('resources.show', $resource) }}" class="hover:underline" wire:navigate>{{ $resource->name }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $resource->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $resource->spaces_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :value="$resource->status" :label="Catalog::label(Catalog::RESOURCE_STATUSES, $resource->status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <x-btn variant="secondary" :href="route('resources.show', $resource)">Ver</x-btn>
                                @if (auth()->user()->isAdmin())
                                    <x-btn variant="secondary" :href="route('resources.edit', $resource)">Editar</x-btn>
                                    <form method="POST" action="{{ route('resources.destroy', $resource) }}"
                                        onsubmit="return confirm('¿Eliminar {{ $resource->name }} del catálogo?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-btn variant="danger" type="submit">Eliminar</x-btn>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            El catálogo esta vacio.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-page>
