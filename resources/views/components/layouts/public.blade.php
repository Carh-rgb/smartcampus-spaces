@props(['title' => null])

{{-- Layout de las paginas publicas (portada y "Acerca de"). Usa el mismo
     head que la aplicacion para respetar el tema claro/oscuro elegido. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="flex min-h-screen flex-col bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <header class="border-b border-zinc-200 dark:border-zinc-800">
            <nav class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-6 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
                    <x-app-logo-icon class="size-6 fill-current" />
                    {{ config('app.name') }}
                </a>

                <div class="flex flex-wrap items-center gap-1 text-sm">
                    <x-public-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-public-link>
                    <x-public-link :href="route('about')" :active="request()->routeIs('about')">Acerca de</x-public-link>
                    @auth
                        <x-btn :href="route('dashboard')" class="ml-2">Ir al panel</x-btn>
                    @else
                        <x-public-link :href="route('login')">Iniciar sesión</x-public-link>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-12 sm:py-16">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 dark:border-zinc-800">
            <p class="mx-auto max-w-5xl px-6 py-6 text-xs text-zinc-500 dark:text-zinc-400">
                {{ config('app.name') }} · {{ now()->year }}
            </p>
        </footer>
    </body>
</html>
