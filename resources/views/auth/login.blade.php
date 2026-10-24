<x-layout title="Log in">
    <form method="POST" action="{{ route('login') }}" class="mx-auto max-w-sm space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        @csrf

        <h1 class="text-lg font-semibold">Log in</h1>

        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" autofocus
                   class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password"
                   class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700">
            Log in
        </button>
    </form>
</x-layout>
