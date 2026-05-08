<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Pasahitza berretsi')"
            :description="__('Aplikazioaren eremu segurua da hau. Mesedez, berretsi zure pasahitza jarraitu aurretik.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store', absolute: false) }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('Pasahitza')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Pasahitza')"
                viewable
            />

            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-[#6366F1] bg-white hover:bg-zinc-100 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white" data-test="confirm-password-button">
                {{ __('Berretsi') }}
            </button>
        </form>
    </div>
</x-layouts.auth>
