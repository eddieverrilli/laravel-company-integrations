@php
    $title = $integration->exists ? 'Edit integration' : 'Add integration';
    $action = $integration->exists
        ? route('companies.integrations.update', [$company, $integration])
        : route('companies.integrations.store', $company);
@endphp

<x-layout :title="$title">
    <a href="{{ route('companies.show', $company) }}" class="text-sm text-zinc-500 hover:text-zinc-900">← {{ $company->name }}</a>

    <h1 class="mt-2 text-2xl font-semibold">{{ $title }}</h1>

    @fragment('form')
        {{-- htmx submits the form; method and action only keep a no-JS submit off the URL. --}}
        <form method="POST" action="{{ $action }}" hx-post="{{ $action }}" hx-swap="outerHTML"
              hx-disabled-elt="find button[type=submit]"
              class="mt-6 space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            @csrf
            @if ($integration->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input id="name" name="name" value="{{ $integration->name }}"
                       class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- The key never goes back to the browser: a saved one shows as its last four,
                 and the input comes back empty after a failed save. --}}
            <div x-data="{ replacing: @js(! $integration->exists || request()->has('api_key')) }">
                <label for="api_key" class="block text-sm font-medium">API key</label>

                @if ($integration->exists)
                    <p x-show="! replacing" class="mt-1 flex items-center gap-3 text-sm">
                        <span class="font-mono">•••• {{ $integration->api_key_last_four }}</span>
                        <button type="button" x-on:click="replacing = true" class="underline">Replace</button>
                    </p>
                @endif

                <template x-if="replacing">
                    <div>
                        <div class="mt-1 flex items-center gap-3">
                            <input id="api_key" name="api_key" type="password" autocomplete="off"
                                   class="block w-full rounded-md border border-zinc-300 px-3 py-2 font-mono">
                            @if ($integration->exists)
                                <button type="button" x-on:click="replacing = false" class="text-sm underline">Cancel</button>
                            @endif
                        </div>
                        @error('api_key') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 disabled:opacity-50">
                    Save
                </button>
                <span class="htmx-indicator text-sm text-zinc-500">Saving…</span>
            </div>
        </form>
    @endfragment
</x-layout>
