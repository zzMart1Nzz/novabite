<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadRecoveryCodes();
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();

        Toaster::success(__('Berreskurapen kodeak birsortu dira.'));
    }

    /**
     * Load the recovery codes for the user.
     */
    private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Ezin izan dira berreskurapen kodeak kargatu.');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div
    class="py-6 space-y-6 border shadow-sm rounded-xl border-zinc-200"
    wire:cloak
    x-data="{ showRecoveryCodes: false }"
>
    <div class="px-6 space-y-2">
        <div class="flex items-center gap-2">
            <flux:icon.lock-closed variant="outline" class="size-4"/>
            <flux:heading size="lg" level="3">{{ __('2FA Berreskurapen Kodeak') }}</flux:heading>
        </div>
        <flux:text variant="subtle">
            {{ __('Berreskurapen kodeek zure 2FA gailua galtzen baduzu sarbidea berreskuratzeko aukera ematen dizute. Gorde itzazu pasahitz kudeatzaile seguru batean.') }}
        </flux:text>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button
                x-show="!showRecoveryCodes"
                class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-6 py-2 font-semibold text-white hover:bg-[#4F46E5] transition shadow-sm focus:outline-none focus:ring-2 focus:ring-[#6366F1] focus:ring-offset-2"
                @click="showRecoveryCodes = true;"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
            >
                <flux:icon.eye variant="outline" class="size-4 mr-2"/>
                {{ __('Ikusi berreskurapen kodeak') }}
            </button>

            <button
                x-show="showRecoveryCodes"
                class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-6 py-2 font-semibold text-white hover:bg-[#4F46E5] transition shadow-sm focus:outline-none focus:ring-2 focus:ring-[#6366F1] focus:ring-offset-2"
                @click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
            >
                {{ __('Ezkutatu berreskurapen kodeak') }}
            </button>

            @if (filled($recoveryCodes))
                <button
                    x-show="showRecoveryCodes"
                    class="inline-flex items-center justify-center rounded-xl bg-zinc-200 px-6 py-2 font-semibold text-zinc-800 hover:bg-zinc-300 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2"
                    wire:click="regenerateRecoveryCodes"
                >
                    <flux:icon.arrow-path variant="outline" class="size-4 mr-2"/>
                    {{ __('Kodeak birsortu') }}
                </button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <flux:callout variant="danger" icon="x-circle" heading="{{$message}}"/>
                @enderror

                @if (filled($recoveryCodes))
                    <div
                        class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-zinc-100"
                        role="list"
                        aria-label="Berreskurapen kodeak"
                    >
                        @foreach($recoveryCodes as $code)
                            <div
                                role="listitem"
                                class="select-text"
                                wire:loading.class="opacity-50 animate-pulse"
                            >
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <flux:text variant="subtle" class="text-xs">
                        {{ __('Berreskurapen kode bakoitza behin erabil daiteke zure kontura sartzeko eta erabili ondoren ezabatu egingo da. Gehiago behar badituzu, sakatu Kodeak birsortu goian.') }}
                    </flux:text>
                @endif
            </div>
        </div>
    </div>
</div>
