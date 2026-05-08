<x-layouts.public>

        <section class="relative overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-white via-zinc-50 to-indigo-50"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-24 sm:py-32">
                <div class="max-w-3xl">
                    <h1 class="text-5xl sm:text-6xl font-extrabold text-slate-900 leading-tight tracking-tight">
                        Etxeko janaria, <br/>benetako zaporea.
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-zinc-700 leading-relaxed">
                        <strong>Nova Bitesen</strong> plater errazak eta ondo eginak eskaintzen ditugu, kalitatezko osagaiekin eta zerbitzu zainduarekin.
                        Ezagutu gure menua eta erreserbatu mahaia segundotan.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('menu', absolute: false) }}" class="inline-flex items-center justify-center rounded-full bg-white px-8 py-4 text-lg font-bold text-[#6366F1] shadow-lg hover:bg-zinc-100 transition transform hover:-translate-y-1" wire:navigate>
                            Menua ikusi
                        </a>
                        @auth
                            <a href="{{ route('reservas', absolute: false) }}" class="inline-flex items-center justify-center rounded-full bg-[#4E8EF7] px-8 py-4 text-lg font-bold text-white shadow-lg hover:opacity-95 transition transform hover:-translate-y-1" wire:navigate>
                                Mahaia erreserbatu
                            </a>
                        @else
                            <a href="{{ route('login', absolute: false) }}" class="inline-flex items-center justify-center rounded-full bg-[#4E8EF7] px-8 py-4 text-lg font-bold text-white shadow-lg hover:opacity-95 transition transform hover:-translate-y-1" wire:navigate>
                                Saioa hasi erreserbatzeko
                            </a>
                        @endauth
                    </div>

                    <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="rounded-2xl bg-white p-6 text-slate-900 shadow-sm border border-zinc-200">
                            <div class="text-sm font-medium uppercase tracking-wider text-zinc-500">Produktua</div>
                            <div class="mt-2 text-xl font-bold">Kalitatezko osagaiak</div>
                        </div>
                        <div class="rounded-2xl bg-white p-6 text-slate-900 shadow-sm border border-zinc-200">
                            <div class="text-sm font-medium uppercase tracking-wider text-zinc-500">Sukaldea</div>
                            <div class="mt-2 text-xl font-bold">Sukaldaritza zintzoa</div>
                        </div>
                        <div class="rounded-2xl bg-white p-6 text-slate-900 shadow-sm border border-zinc-200">
                            <div class="text-sm font-medium uppercase tracking-wider text-zinc-500">Erreserba</div>
                            <div class="mt-2 text-xl font-bold">Aukeratu data eta txanda</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-14">
            <div class="grid grid-cols-1 nb-desktop-two-grid gap-10 items-start">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold">Gure filosofia</h2>
                    <p class="mt-3 text-zinc-600">
                        Garrantzitsuena xehetasunetan dago: produktu ona, errezeta argiak eta giro atsegina.
                        Karta labur baina ondo pentsatua, eguneko une bakoitzerako aukerekin.
                    </p>
                    <a href="{{ route('filosofia', absolute: false) }}" class="mt-5 inline-flex items-center font-semibold text-[#6366F1] hover:underline" wire:navigate>
                        Gehiago jakin
                    </a>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-6">
                    <h3 class="text-lg font-semibold">Etortzeko prest?</h3>
                    <p class="mt-2 text-zinc-600">
                        6 mahai eta bi txanda ditugu: bazkaria eta afaria. Erreserba bakoitza 2 ordukoa da. Mahai libreak berdez agertzen dira; zure erreserbak urdinez, eta beste erabiltzaileenak gorriz.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('reservas', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-5 py-3 font-semibold text-white hover:bg-[#4F46E5]" wire:navigate>
                                Erreserbatu orain
                            </a>
                        @else
                            <a href="{{ route('register', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-5 py-3 font-semibold text-white hover:bg-[#4F46E5]" wire:navigate>
                                Kontua sortu
                            </a>
                        @endauth
                        <a href="{{ route('restaurantes', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-5 py-3 font-semibold hover:bg-zinc-50" wire:navigate>
                            Jatetxeak ikusi
                        </a>
                    </div>
                </div>
            </div>
        </section>

</x-layouts.public>
