<x-page title="Editar solicitud" subtitle="Solo se puede modificar mientras siga pendiente de aprobación."
    :back="route('reservations.show', $reservation)">

    <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="flex flex-col gap-5">
        @csrf
        @method('PUT')

        @include('reservations.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar cambios</x-btn>
            <x-btn variant="secondary" :href="route('reservations.show', $reservation)">Cancelar</x-btn>
        </div>
    </form>
</x-page>
