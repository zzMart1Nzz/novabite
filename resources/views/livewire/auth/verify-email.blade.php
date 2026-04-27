<x-layouts.auth>
    <div class="mt-4 flex flex-col gap-6">
        <flux:text class="text-center">
            {{ __('Mesedez, egiaztatu zure helbide elektronikoa bidali dizugun estekan klik eginez.') }}
        </flux:text>

        @if (session('status') == 'verification-link-sent')
            <div x-data x-init="$dispatch('toaster:received', { type: 'success', message: '{{ __('Egiaztapen esteka berri bat bidali da erregistroan eman duzun helbide elektronikora.') }}' })"></div>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: #6366F1;">
                    {{ __('Egiaztapen mezua berriro bidali') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm cursor-pointer underline text-gray-600 hover:text-gray-900" data-test="logout-button">
                    {{ __('Saioa itxi') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
