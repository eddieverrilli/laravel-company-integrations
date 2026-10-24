<x-layout :title="$company->name">
    <h1 class="text-2xl font-semibold">{{ $company->name }}</h1>

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
