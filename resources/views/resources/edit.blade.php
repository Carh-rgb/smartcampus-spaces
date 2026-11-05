<x-page :title="'Editar: '.$resource->name" subtitle="Actualiza los datos del recurso."
    :back="route('resources.index')">

    <form method="POST" action="{{ route('resources.update', $resource) }}" class="flex flex-col gap-5">
        @csrf
        @method('PUT')

        @include('resources.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar cambios</x-btn>
            <x-btn variant="secondary" :href="route('resources.index')">Cancelar</x-btn>
        </div>
    </form>
</x-page>
