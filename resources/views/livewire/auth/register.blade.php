<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Sortu kontua')" :description="__('Sartu zure datuak kontua sortzeko')" />

        <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm leading-relaxed text-white">
            {{ __('Kontua sortu ondoren, posta elektronikoa egiaztatzeko esteka bidaliko dizugu. Mahaia erreserbatzeko posta egiaztatuta eduki behar da.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store', absolute: false) }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Izena')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Izen osoa')"
            />

            <flux:input
                name="telefonoa"
                :label="__('Telefonoa')"
                :value="old('telefonoa')"
                type="tel"
                required
                autocomplete="tel"
                :placeholder="__('Telefono zenbakia')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Posta elektronikoa')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Pasahitza')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Pasahitza')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Pasahitza berretsi')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Pasahitza berretsi')"
                viewable
            />

            <div class="flex items-center justify-end">
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-[#6366F1] bg-white hover:bg-zinc-100 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white" data-test="register-user-button">
                    {{ __('Sortu kontua') }}
                </button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-white">
            <span>{{ __('Badaukazu konturik?') }}</span>
            <a href="{{ route('login', absolute: false) }}" class="font-bold hover:underline" wire:navigate>{{ __('Hasi saioa') }}</a>
        </div>
    </div>
</x-layouts.auth>
