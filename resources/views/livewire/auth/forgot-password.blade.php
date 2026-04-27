<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Pasahitza ahaztu duzu')" :description="__('Sartu zure posta elektronikoa pasahitza berrezartzeko esteka jasotzeko')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Posta elektronikoa')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-[#6366F1] bg-white hover:bg-zinc-100 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white" data-test="email-password-reset-link-button">
                {{ __('Bidali pasahitza berrezartzeko esteka') }}
            </button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-white">
            <span>{{ __('Edo, itzuli') }}</span>
            <a href="{{ route('login') }}" class="font-bold hover:underline" wire:navigate>{{ __('saioa hasi') }}</a>
        </div>
    </div>
</x-layouts.auth>
