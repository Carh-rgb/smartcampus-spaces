@props(['value' => null, 'label' => null])

@php
    // Tono segun el significado del estado, no segun la entidad:
    // verde = todo en orden, ambar = requiere atención, rojo = bloqueado.
    $tones = [
        'available' => 'emerald',
        'operational' => 'emerald',
        'approved' => 'emerald',
        'resolved' => 'emerald',
        'maintenance' => 'amber',
        'pending' => 'amber',
        'in_progress' => 'amber',
        'medium' => 'amber',
        'out_of_service' => 'red',
        'rejected' => 'red',
        'open' => 'red',
        'high' => 'red',
        'inactive' => 'zinc',
        'cancelled' => 'zinc',
        'low' => 'zinc',
    ];

    $classes = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/30',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/30',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-400/30',
        'zinc' => 'bg-zinc-100 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-700/40 dark:text-zinc-300 dark:ring-zinc-500/30',
    ];

    $tone = $classes[$tones[$value] ?? 'zinc'];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', $tone]) }}>
    {{ $label ?? $value }}
</span>
