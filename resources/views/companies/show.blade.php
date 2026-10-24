<x-layout :title="$company->name">
    <h1 class="text-2xl font-semibold">{{ $company->name }}</h1>

    <section class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-medium uppercase tracking-wide text-zinc-500">Integrations</h2>
            <a href="{{ route('companies.integrations.create', $company) }}"
               class="rounded-md bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-700">Add integration</a>
        </div>

        <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
            @forelse ($integrations as $integration)
                <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                    <span>{{ $integration->name }}</span>
                    <span class="flex items-center gap-4">
                        <span class="font-mono text-zinc-500">•••• {{ $integration->api_key_last_four }}</span>
                        <a href="{{ route('companies.integrations.edit', [$company, $integration]) }}" class="underline">Edit</a>
                    </span>
                </li>
            @empty
                <li class="px-4 py-3 text-sm text-zinc-500">No integrations yet.</li>
            @endforelse
        </ul>
    </section>

    <section class="mt-10">
        <h2 class="text-sm font-medium uppercase tracking-wide text-zinc-500">Members</h2>

        <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 bg-white">
            @foreach ($users as $user)
                <li class="flex justify-between gap-4 px-4 py-3 text-sm">
                    <span>{{ $user->name }}</span>
                    <span class="text-zinc-500">{{ $user->email }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</x-layout>
