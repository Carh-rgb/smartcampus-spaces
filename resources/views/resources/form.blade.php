@use(App\Support\Catalog)

<div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="grid gap-5 md:grid-cols-2">
        <x-input name="name" label="Nombre del recurso" :value="$resource->name" required maxlength="100"
            help="Por ejemplo: Video Beam, Tablero Inteligente." />

        <x-select name="status" label="Estado" :options="Catalog::RESOURCE_STATUSES"
            :selected="$resource->status ?? 'operational'" required />
    </div>

    <div class="mt-5">
        <x-textarea name="description" label="Descripción (opcional)" :value="$resource->description" rows="2"
            maxlength="255" />
    </div>
</div>
