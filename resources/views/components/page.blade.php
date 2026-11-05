@props(['title', 'subtitle' => null, 'back' => null])

<x-layouts.app :title="$title">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                @if ($back)
                    <a href="{{ $back }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100" wire:navigate>
                        &larr; Volver
                    </a>
                @endif
                <h1 class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>

        <x-flash />

        {{ $slot }}
    </div>
</x-layouts.app>
