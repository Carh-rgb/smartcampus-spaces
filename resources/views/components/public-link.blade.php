@props(['href', 'active' => false])

<a href="{{ $href }}" @class([
    'rounded-lg px-3 py-2 transition',
    'bg-zinc-100 font-medium text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' => $active,
    'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100' => ! $active,
])>{{ $slot }}</a>
