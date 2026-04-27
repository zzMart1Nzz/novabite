<x-layouts.public>
    <section class="mx-auto max-w-6xl px-4 py-16 sm:py-24">
        <div class="text-center mb-16">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">Jatetxeak</h1>
            <p class="mt-4 text-xl text-zinc-600 max-w-2xl mx-auto">
                Zatoz gure jatetxera eta gozatu Nova Bites esperientziaz.
            </p>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="group rounded-3xl border border-[#E5E7EB] bg-white p-8 shadow-sm hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Nova Bites</h2>
                <div class="space-y-3 text-zinc-600">
                    <p class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-[#6366F1] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Argi Kalea 12, 20240 Ordizia, Gipuzkoa</span>
                    </p>
                    <p class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-[#6366F1] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>13:00–16:00 / 20:00–23:30</span>
                    </p>
                    <p class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-[#6366F1] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>+34 943 00 00 00</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-16 rounded-3xl bg-[#F9FAFB] p-10 border border-[#E5E7EB] text-center">
            <h3 class="text-2xl font-bold text-slate-900">Mahaia erreserbatu nahi duzu?</h3>
            <p class="mt-4 text-zinc-600 max-w-2xl mx-auto text-lg">
                Saioa hasi data eta txanda aukeratzeko. Eskuragarritasuna unean-unean erakutsiko dizugu.
            </p>
            <div class="mt-8">
                @auth
                    <a href="{{ route('reservas') }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-10 py-4 text-lg font-bold text-white shadow-lg hover:bg-[#4F46E5] transition transform hover:-translate-y-1" wire:navigate>
                        Mahaia erreserbatu
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-10 py-4 text-lg font-bold text-white shadow-lg hover:bg-[#4F46E5] transition transform hover:-translate-y-1" wire:navigate>
                        Saioa hasi erreserbatzeko
                    </a>
                @endauth
            </div>
        </div>
    </section>
</x-layouts.public>
