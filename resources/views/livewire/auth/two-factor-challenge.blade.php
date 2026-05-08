<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;

                    this.code = '';
                    this.recovery_code = '';

                    $dispatch('clear-2fa-auth-code');
            
                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code');
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <x-auth-header
                    :title="__('Autentifikazio Kodea')"
                    :description="__('Sartu zure autentifikatzaile aplikazioak emandako kodea.')"
                />
            </div>

            <div x-show="showRecoveryInput">
                <x-auth-header
                    :title="__('Berreskurapen Kodea')"
                    :description="__('Mesedez, berretsi zure konturako sarbidea zure larrialdietarako berreskurapen kodeetako bat sartuz.')"
                />
            </div>

            <form method="POST" action="{{ route('two-factor.login.store', absolute: false) }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="flex items-center justify-center my-5">
                            <x-input-otp
                                name="code"
                                digits="6"
                                autocomplete="one-time-code"
                                x-model="code"
                            />
                        </div>

                        @error('code')
                            <flux:text color="red">
                                {{ $message }}
                            </flux:text>
                        @enderror
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-5">
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                            />
                        </div>

                        @error('recovery_code')
                            <flux:text color="red">
                                {{ $message }}
                            </flux:text>
                        @enderror
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: #6366F1;">
                        {{ __('Jarraitu') }}
                    </button>
                </div>

                <div class="mt-5 space-x-0.5 text-sm leading-5 text-center">
                    <span class="opacity-50">{{ __('edo bestela') }}</span>
                    <div class="inline font-medium underline cursor-pointer opacity-80">
                        <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('hasi saioa berreskurapen kode batekin') }}</span>
                        <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('hasi saioa autentifikazio kode batekin') }}</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.auth>
