@use(App\Support\Catalog)

{{-- Formulario compartido por create y edit: el metodo y la accion
     los define cada vista antes de incluirlo. --}}
<div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="grid gap-5 md:grid-cols-2">
        <x-input name="name" label="Nombre del espacio" :value="$space->name" required maxlength="100"
            help="Debe ser unico, por ejemplo: Laboratorio de Computo 1." />

        <x-input name="building" label="Bloque o ubicacion" :value="$space->building" required maxlength="50" />

        <x-input name="capacity" label="Capacidad (personas)" type="number" :value="$space->capacity" required min="1"
            max="1000" />

        <x-select name="type" label="Tipo de espacio" :options="Catalog::SPACE_TYPES" :selected="$space->type" required />

        <x-select name="status" label="Estado" :options="Catalog::SPACE_STATUSES" :selected="$space->status ?? 'available'"
            required help="Un espacio en mantenimiento no aparece al crear reservas." />
    </div>

    <div class="mt-5">
        <x-textarea name="description" label="Descripcion (opcional)" :value="$space->description" rows="3"
            maxlength="500" />
    </div>
</div>
