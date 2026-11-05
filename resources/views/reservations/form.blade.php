@php
    $spaceOptions = $spaces
        ->mapWithKeys(fn($s) => [$s->id => $s->name.' — '.$s->building.' (cap. '.$s->capacity.')'])
        ->all();

    $selectedSpace = $reservation->space_id ?? request('space_id');
@endphp

<div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <x-select name="space_id" label="Espacio" :options="$spaceOptions" :selected="$selectedSpace"
                placeholder="Selecciona un espacio..." required
                help="Solo aparecen los espacios disponibles; los que estan en mantenimiento no se pueden reservar." />
        </div>

        <x-input name="date" label="Fecha" type="date" :value="$reservation->date?->format('Y-m-d')" required
            min="{{ now()->toDateString() }}" />

        <div class="grid grid-cols-2 gap-4">
            <x-input name="start_time" label="Hora de inicio" type="time"
                :value="$reservation->start_time ? substr($reservation->start_time, 0, 5) : null" required />

            <x-input name="end_time" label="Hora de fin" type="time"
                :value="$reservation->end_time ? substr($reservation->end_time, 0, 5) : null" required />
        </div>
    </div>

    <div class="mt-5">
        <x-textarea name="purpose" label="Razon de uso" :value="$reservation->purpose" rows="2" maxlength="255" required
            help="Por ejemplo: Clase practica de Bases de Datos II." />
    </div>
</div>
