<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Hasi saioa zure kontuan')" :description="__('Sartu zure posta elektronikoa eta pasahitza saioa hasteko')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store', absolute: false) }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Posta elektronikoa')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Pasahitza')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Pasahitza')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request', absolute: false)" wire:navigate>
                        {{ __('Pasahitza ahaztu duzu?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->


            <div class="flex items-center justify-end">
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-[#6366F1] bg-white hover:bg-zinc-100 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white" data-test="login-button">
                    {{ __('Hasi saioa') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-white">
                <span>{{ __('Ez daukazu konturik?') }}</span>
                <a href="{{ route('register', absolute: false) }}" class="font-bold hover:underline" wire:navigate>{{ __('Erregistratu') }}</a>
            </div>
        @endif
    </div>
</x-layouts.auth>
