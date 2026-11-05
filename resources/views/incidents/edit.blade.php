<x-page title="Editar reporte" subtitle="Corrige los datos de la novedad reportada."
    :back="route('incidents.show', $incident)">

    <form method="POST" action="{{ route('incidents.update', $incident) }}" class="flex flex-col gap-5">
        @csrf
        @method('PUT')

        @include('incidents.form')

        <div class="flex gap-2">
            <x-btn type="submit">Guardar cambios</x-btn>
            <x-btn variant="secondary" :href="route('incidents.show', $incident)">Cancelar</x-btn>
        </div>
    </form>
</x-page>
