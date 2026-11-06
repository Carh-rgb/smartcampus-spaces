{{-- Resumen de los errores de validación del formulario. Los mensajes de
     éxito y de error de las operaciones se muestran como toast (x-toast). --}}
@if ($errors->any())
    <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
        <p class="font-medium">Revisa los datos del formulario:</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
