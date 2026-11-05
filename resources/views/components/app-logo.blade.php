<div class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
    <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
</div>
<div class="ml-1 grid flex-1 text-left text-sm">
    <span class="mb-0.5 truncate leading-none font-semibold">{{ config('app.name') }}</span>

    {{-- La entidad que despliega el sistema se configura en el .env --}}
    @if (config('app.organization'))
        <span class="truncate text-xs leading-none text-zinc-500 dark:text-zinc-400">{{ config('app.organization') }}</span>
    @endif
</div>
