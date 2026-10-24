@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
        @auth
            <header class="border-b border-zinc-200 bg-white">
                <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                    <a href="{{ route('home') }}" class="font-semibold">{{ auth()->user()->company->name }}</a>

                    <form method="POST" action="{{ route('logout') }}" class="flex items-center gap-4 text-sm text-zinc-600">
                        @csrf
                        <span>{{ auth()->user()->name }}</span>
                        <button type="submit" class="underline hover:text-zinc-900">Log out</button>
                    </form>
                </div>
            </header>
        @endauth

        <main class="mx-auto max-w-3xl px-6 py-10">
            {{ $slot }}
        </main>
    </body>
</html>
