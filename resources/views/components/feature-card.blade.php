@props(['title'])

<div {{ $attributes->class('rounded-xl border border-zinc-200 p-4 dark:border-zinc-700') }}>
    <p class="font-medium">{{ $title }}</p>
    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $slot }}</p>
</div>
