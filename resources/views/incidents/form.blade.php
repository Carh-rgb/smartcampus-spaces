@use(App\Support\Catalog)

@php
    $reservationOptions = $reservations
        ->mapWithKeys(fn($r) => [
            $r->id => $r->space->name.' — '.$r->date->format('d/m/Y').' '.substr($r->start_time, 0, 5),
        ])
        ->all();

    $selectedReservation = $incident->reservation_id ?? ($selected ?? request('reservation_id'));
@endphp

<div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <x-select name="reservation_id" label="Reserva afectada" :options="$reservationOptions"
            :selected="$selectedReservation" placeholder="Selecciona la reserva..." required
            help="Un incidente siempre se reporta sobre una reserva ya realizada." />

        <x-select name="priority" label="Prioridad" :options="Catalog::PRIORITIES"
            :selected="$incident->priority ?? 'medium'" required />

        <div class="md:col-span-2">
            <x-input name="title" label="Título" :value="$incident->title" required maxlength="120"
                help="Por ejemplo: Video beam no enciende." />
        </div>
    </div>

    <div class="mt-5">
        <x-textarea name="description" label="Descripción de la novedad" :value="$incident->description" rows="4"
            maxlength="1000" required />
    </div>
</div>
