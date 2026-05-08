<x-layouts.public>
    <section class="mx-auto max-w-6xl px-4 py-16 sm:py-24">
        <div class="text-center mb-16">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-zinc-900 tracking-tight">Gure Filosofia</h1>
            <p class="mt-4 text-xl text-zinc-600 max-w-2xl mx-auto">
                Nova Bitesen sinpletasun ondo eginean sinesten dugu: produktu ona, sukaldaritza zaindua eta esperientzia atsegina.
            </p>
        </div>

        <div class="grid grid-cols-1 nb-desktop-three-grid gap-8">
            <div class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-12 h-12 bg-[#6366F1]/10 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h2 class="font-bold text-2xl text-zinc-900 mb-3">Produktua</h2>
                <p class="text-zinc-600 leading-relaxed">
                    Hornitzaile fidagarriekin eta produktu sasoikoekin lan egiten dugu. Karta eguneratzen dugu beti onena eskaintzeko.
                </p>
            </div>
            <div class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-12 h-12 bg-[#6366F1]/10 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h2 class="font-bold text-2xl text-zinc-900 mb-3">Sukaldea</h2>
                <p class="text-zinc-600 leading-relaxed">
                    Errezeta argiak, puntua eta zaporeen oreka. Gutxiago gehiago da gauzak ondo eginda daudenean.
                </p>
            </div>
            <div class="rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm hover:shadow-lg transition duration-300">
                <div class="w-12 h-12 bg-[#6366F1]/10 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="font-bold text-2xl text-zinc-900 mb-3">Esperientzia</h2>
                <p class="text-zinc-600 leading-relaxed">
                    Espazio atsegina eta erreserba azkarrak: aukeratu data, txanda eta mahaia segundotan. Zure erosotasuna gure lehentasuna da.
                </p>
            </div>
        </div>

        <div class="mt-16 flex flex-wrap justify-center gap-4">
            <a href="{{ route('menu', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-zinc-200 px-8 py-3 font-semibold text-zinc-700 hover:bg-zinc-50 transition shadow-sm" wire:navigate>
                Menua ikusi
            </a>
            @auth
                <a href="{{ route('reservas', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-8 py-3 font-semibold text-white hover:bg-[#4F46E5] transition shadow-lg hover:-translate-y-0.5" wire:navigate>
                    Mahaia erreserbatu
                </a>
            @endauth
        </div>
    </section>
</x-layouts.public>
