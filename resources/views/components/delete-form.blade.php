@props(['action', 'confirm', 'label' => 'Eliminar', 'variant' => 'button'])

{{-- Formulario de eliminación reutilizable: envía DELETE con @csrf y pide
     confirmación. Js::from escapa el mensaje, así un nombre con comillas
     o apóstrofos no rompe la confirmación. --}}
<form method="POST" action="{{ $action }}" onsubmit="return confirm({{ Js::from($confirm) }})" {{ $attributes }}>
    @csrf
    @method('DELETE')

    @if ($variant === 'link')
        <button type="submit" class="text-xs font-medium text-red-600 hover:underline dark:text-red-400">{{ $label }}</button>
    @else
        <x-btn variant="danger" type="submit">{{ $label }}</x-btn>
    @endif
</form>
