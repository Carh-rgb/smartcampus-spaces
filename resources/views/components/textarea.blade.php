@props(['name', 'label', 'value' => null, 'rows' => 3, 'help' => null])

<div class="flex flex-col gap-1.5">
    <label for="{{ $name }}" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->class([
            'w-full rounded-lg border bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-500 dark:bg-zinc-800 dark:text-zinc-100',
            'border-zinc-300 dark:border-zinc-600' => !$errors->has($name),
            'border-red-400 dark:border-red-500' => $errors->has($name),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($help)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
