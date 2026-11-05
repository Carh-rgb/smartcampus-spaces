<x-page title="Reportar incidente" subtitle="Registra una falla o novedad sobre un espacio que tenias reservado."
    :back="route('incidents.index')">

    @if ($reservations->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                No tienes reservas aprobadas sobre las que reportar. Primero solicita un espacio y espera la aprobación.
            </p>
            <div class="mt-4 flex justify-center">
                <x-btn :href="route('reservations.create')">Crear una reserva</x-btn>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('incidents.store') }}" class="flex flex-col gap-5">
            @csrf

            @include('incidents.form')

            <div class="flex gap-2">
                <x-btn type="submit">Enviar reporte</x-btn>
                <x-btn variant="secondary" :href="route('incidents.index')">Cancelar</x-btn>
            </div>
        </form>
    @endif
</x-page>
