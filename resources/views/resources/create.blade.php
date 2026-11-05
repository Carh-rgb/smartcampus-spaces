<x-page title="Nuevo recurso" subtitle="Agrega un equipo al catalogo." :back="route('resources.index')">

    <form method="POST" action="{{ route('resources.store') }}" class="flex flex-col gap-5">
        @csrf

        @include('resources.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar recurso</x-btn>
            <x-btn variant="secondary" :href="route('resources.index')">Cancelar</x-btn>
        </div>
    </form>
</x-page>
