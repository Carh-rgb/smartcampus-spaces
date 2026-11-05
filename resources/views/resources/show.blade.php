@use(App\Support\Catalog)

<x-page :title="$resource->name" :subtitle="$resource->description" :back="route('resources.index')">
    <x-slot:actions>
        <x-badge :value="$resource->status" :label="Catalog::label(Catalog::RESOURCE_STATUSES, $resource->status)" />
        @if (auth()->user()->isAdmin())
            <x-btn variant="secondary" :href="route('resources.edit', $resource)">Editar</x-btn>
        @endif
    </x-slot:actions>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Espacios donde está instalado</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Resultado de la relación muchos a muchos.</p>
        </div>

        <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($resource->spaces as $space)
                <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                    <a href="{{ route('spaces.show', $space) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                        {{ $space->name }}
                    </a>
                    <span class="text-zinc-600 dark:text-zinc-400">
                        {{ $space->building }} · {{ $space->pivot->quantity }} unidad(es)
                    </span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    Este recurso no está asignado a ningún espacio.
                </li>
            @endforelse
        </ul>
    </div>
</x-page>
