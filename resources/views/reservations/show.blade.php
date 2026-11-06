@use(App\Support\Catalog)

<x-page :title="'Reserva · '.$reservation->space->name"
    :subtitle="$reservation->date->format('d/m/Y').' de '.substr($reservation->start_time, 0, 5).' a '.substr($reservation->end_time, 0, 5)"
    :back="route('reservations.index')">

    <x-slot:actions>
        <x-badge :value="$reservation->status" :label="Catalog::label(Catalog::RESERVATION_STATUSES, $reservation->status)" />

        @can('update', $reservation)
            <x-btn variant="secondary" :href="route('reservations.edit', $reservation)">Editar</x-btn>
        @endcan

        @can('cancel', $reservation)
            <form method="POST" action="{{ route('reservations.cancel', $reservation) }}"
                onsubmit="return confirm('¿Cancelar esta reserva y liberar el espacio?')">
                @csrf
                @method('PATCH')
                <x-btn variant="secondary" type="submit">Cancelar reserva</x-btn>
            </form>
        @endcan
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Datos de la solicitud</h2>

            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Solicitante</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">
                        {{ $reservation->user->name }}
                        <span class="text-zinc-500 dark:text-zinc-400">({{ Catalog::label(Catalog::ROLES, $reservation->user->role) }})</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Espacio</dt>
                    <dd>
                        <a href="{{ route('spaces.show', $reservation->space) }}" class="text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                            {{ $reservation->space->name }} · {{ $reservation->space->building }}
                        </a>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Razón de uso</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $reservation->purpose }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Revisada por</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $reservation->approver?->name ?? 'Sin revisar' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Fecha de revisión</dt>
                    <dd class="text-zinc-900 dark:text-zinc-100">{{ $reservation->reviewed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                @if ($reservation->review_notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Observación</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $reservation->review_notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Panel de decisión: solo para el administrador y solo si sigue pendiente --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            @can('review', $reservation)
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Decisión</h2>

                <form method="POST" action="{{ route('reservations.approve', $reservation) }}" class="mb-5 flex flex-col gap-3">
                    @csrf
                    @method('PATCH')
                    <x-input name="review_notes" label="Observación (opcional)" maxlength="500" />
                    <x-btn variant="success" type="submit">Aprobar reserva</x-btn>
                </form>

                <form method="POST" action="{{ route('reservations.reject', $reservation) }}" class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                    @csrf
                    @method('PATCH')
                    <x-textarea name="review_notes" label="Motivo del rechazo" rows="2" maxlength="500" required />
                    <x-btn variant="danger" type="submit">Rechazar</x-btn>
                </form>
            @else
                <h2 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Incidentes</h2>
                <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
                    Novedades reportadas sobre esta reserva.
                </p>
                <x-btn variant="secondary" :href="route('incidents.create', ['reservation_id' => $reservation->id])">
                    Reportar incidente
                </x-btn>
            @endcan
        </div>
    </div>

    @if ($reservation->incidents->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Incidentes de esta reserva</h2>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($reservation->incidents as $incident)
                    <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('incidents.show', $incident) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100" wire:navigate>
                                {{ $incident->title }}
                            </a>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                Reportado por {{ $incident->user->name }} · {{ $incident->reported_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-badge :value="$incident->priority" :label="'Prioridad '.Catalog::label(Catalog::PRIORITIES, $incident->priority)" />
                            <x-badge :value="$incident->status" :label="Catalog::label(Catalog::INCIDENT_STATUSES, $incident->status)" />
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</x-page>
