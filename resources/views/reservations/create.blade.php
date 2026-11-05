<x-page title="Nueva reserva" subtitle="La solicitud queda pendiente hasta que el administrador la apruebe."
    :back="route('reservations.index')">

    <form method="POST" action="{{ route('reservations.store') }}" class="flex flex-col gap-5">
        @csrf

        @include('reservations.form')

        <div class="flex gap-2">
            <x-btn type="submit">Enviar solicitud</x-btn>
            <x-btn variant="secondary" :href="route('reservations.index')">Cancelar</x-btn>
        </div>
    </form>
</x-page>
