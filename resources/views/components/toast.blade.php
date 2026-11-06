{{-- Notificaciones toast de los mensajes de sesión (flash) que dejan los
     controladores con ->with('success', ...) o ->with('error', ...).
     Se cierran con la X o solas a los pocos segundos (Alpine.js, incluido
     con Livewire). Sin JavaScript se quedan visibles como un aviso normal. --}}
@php
    $toasts = collect([
        'success' => session('success'),
        'error' => session('error'),
    ])->filter();
@endphp

@if ($toasts->isNotEmpty())
    <div class="pointer-events-none fixed inset-x-4 top-4 z-50 flex flex-col gap-3 sm:inset-x-auto sm:right-6 sm:top-6 sm:w-96">
        @foreach ($toasts as $type => $message)
            @php $ok = $type === 'success'; @endphp

            <div x-data="{ open: true }" x-init="setTimeout(() => open = false, {{ $ok ? 5000 : 8000 }})" x-show="open"
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0"
                role="{{ $ok ? 'status' : 'alert' }}" data-toast="{{ $type }}"
                @class([
                    'pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-4 shadow-lg dark:bg-zinc-900',
                    'border-emerald-200 dark:border-emerald-500/40' => $ok,
                    'border-red-200 dark:border-red-500/40' => ! $ok,
                ])>
                <span @class([
                    'mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' => $ok,
                    'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' => ! $ok,
                ]) aria-hidden="true">{{ $ok ? '✓' : '!' }}</span>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $ok ? 'Listo' : 'No se pudo completar' }}</p>
                    <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-300">{{ $message }}</p>
                </div>

                <button type="button" x-on:click="open = false" aria-label="Cerrar notificación"
                    class="-m-1 rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
