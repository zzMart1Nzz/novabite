<x-layouts.auth>
    <div class="mt-4 flex flex-col gap-6">
        <div class="text-center">
            <flux:heading size="xl">{{ __('Posta elektronikoa egiaztatu') }}</flux:heading>
            <flux:text class="mt-3">
                {{ __('Mahaia erreserbatu aurretik, egiaztatu zure helbide elektronikoa bidali dizugun estekan klik eginez.') }}
            </flux:text>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ __('Egiaztapen esteka bidali da zure posta elektronikora.') }}
            </div>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send', absolute: false) }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: #6366F1;">
                    {{ __('Egiaztapen mezua berriro bidali') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout', absolute: false) }}">
                @csrf
                <button type="submit" class="text-sm cursor-pointer underline text-gray-600 hover:text-gray-900" data-test="logout-button">
                    {{ __('Saioa itxi') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
