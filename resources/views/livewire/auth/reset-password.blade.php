<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Pasahitza berrezarri')" :description="__('Mesedez, sartu zure pasahitz berria behean')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                :label="__('Posta elektronikoa')"
                type="email"
                required
                autocomplete="email"
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
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-[#6366F1] bg-white hover:bg-zinc-100 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white" data-test="reset-password-button">
                    {{ __('Pasahitza berrezarri') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.auth>
