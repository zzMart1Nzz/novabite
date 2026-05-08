<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Toaster::success(__('Pasahitza eguneratuta.'));
    }
}; ?>

<section id="password" class="w-full scroll-mt-28">
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2 text-2xl font-bold">{{ __('Pasahitza eguneratu') }}</flux:heading>
        <flux:subheading>{{ __('Ziurtatu zure kontuak pasahitz luze eta ausazkoa erabiltzen duela seguru mantentzeko') }}</flux:subheading>
    </div>
    
    <form method="POST" wire:submit="updatePassword" class="space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Oraingo pasahitza')"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('Pasahitz berria')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Pasahitza berretsi')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-8 py-3 font-semibold text-white hover:bg-[#4F46E5] transition shadow-md focus:outline-none focus:ring-2 focus:ring-[#6366F1] focus:ring-offset-2" data-test="update-password-button">
                        {{ __('Gorde') }}
                    </button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Gordeta.') }}
                </x-action-message>
            </div>
        </form>
</section>
