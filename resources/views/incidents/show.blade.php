@use(App\Support\Catalog)

<x-page :title="$incident->title"
    :subtitle="$incident->reservation->space->name.' · reserva del '.$incident->reservation->date->format('d/m/Y')"
    :back="route('incidents.index')">

    <x-slot:actions>
        <x-badge :value="$incident->priority" :label="'Prioridad '.Catalog::label(Catalog::PRIORITIES, $incident->priority)" />
        <x-badge :value="$incident->status" :label="Catalog::label(Catalog::INCIDENT_STATUSES, $incident->status)" />

        @if ($incident->status !== 'resolved')
            <x-btn variant="secondary" :href="route('incidents.edit', $incident)">Editar</x-btn>
        @endif

        @if (auth()->user()->isAdmin() || $incident->user_id === auth()->id())
            <form method="POST" action="{{ route('incidents.destroy', $incident) }}"
                onsubmit="return confirm('¿Eliminar este reporte?')">
                @csrf
                @method('DELETE')
                <x-btn variant="danger" type="submit">Eliminar</x-btn>
            </form>
        @endif
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Novedad reportada</h2>

            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $incident->description }}</p>

            <dl class="mt-5 grid gap-4 border-t border-zinc-200 pt-5 text-sm sm:grid-cols-2 dark:border-zinc-700">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Reportado por</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $incident->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Fecha del reporte</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $incident->reported_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Reserva</dt>
                    <dd>
                        <a href="{{ route('reservations.show', $incident->reservation) }}" class="text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                            Ver la reserva
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Responsable</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $incident->assignee?->name ?? 'Sin asignar' }}</dd>
                </div>
                @if ($incident->solution)
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Solución aplicada</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $incident->solution }}</dd>
                        <dd class="text-xs text-zinc-500 dark:text-zinc-400">Cerrado el {{ $incident->resolved_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Gestión</h2>

            @if (auth()->user()->isAdmin() && $incident->status !== 'resolved')
                <form method="POST" action="{{ route('incidents.assign', $incident) }}" class="mb-5 flex flex-col gap-3">
                    @csrf
                    @method('PATCH')
                    <x-select name="assigned_to" label="Asignar a" :options="$admins->pluck('name', 'id')->all()"
                        :selected="$incident->assigned_to" placeholder="Selecciona..." required />
                    <x-btn variant="secondary" type="submit">Asignar responsable</x-btn>
                </form>

                <form method="POST" action="{{ route('incidents.resolve', $incident) }}"
                    class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    @csrf
                    @method('PATCH')
                    <x-textarea name="solution" label="Solución aplicada" rows="3" maxlength="500" required />
                    <x-btn variant="success" type="submit">Marcar como resuelto</x-btn>
                </form>
            @elseif ($incident->status === 'resolved')
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Este incidente ya fue resuelto. No requiere más gestión.
                </p>
            @else
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    El administrador asignará un responsable y registrará la solución.
                </p>
            @endif
        </div>
    </div>
</x-page>
