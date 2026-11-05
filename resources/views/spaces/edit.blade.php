<x-page :title="'Editar: '.$space->name" subtitle="Actualiza los datos del espacio."
    :back="route('spaces.show', $space)">

    <form method="POST" action="{{ route('spaces.update', $space) }}" class="flex flex-col gap-5">
        @csrf
        @method('PUT')

        @include('spaces.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar cambios</x-btn>
            <x-btn variant="secondary" :href="route('spaces.show', $space)">Cancelar</x-btn>
        </div>
    </form>
</x-page>
