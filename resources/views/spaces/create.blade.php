<x-page title="Nuevo espacio" subtitle="Registra un aula, laboratorio, auditorio o sala de reuniones."
    :back="route('spaces.index')">

    <form method="POST" action="{{ route('spaces.store') }}" class="flex flex-col gap-5">
        @csrf

        @include('spaces.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar espacio</x-btn>
            <x-btn variant="secondary" :href="route('spaces.index')">Cancelar</x-btn>
        </div>
    </form>
</x-page>
