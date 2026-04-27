<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        Toaster::success(__('Zure kontua ezabatu da.'));

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Kontua ezabatu') }}</flux:heading>
        <flux:subheading>{{ __('Ezabatu zure kontua eta baliabide guztiak') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <button class="inline-flex items-center justify-center rounded-xl bg-red-600 px-6 py-2 font-semibold text-white hover:opacity-90 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" data-test="delete-user-button">
            {{ __('Kontua ezabatu') }}
        </button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Ziur zaude zure kontua ezabatu nahi duzula?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Behin zure kontua ezabatuta, baliabide eta datu guztiak behin betiko ezabatuko dira. Mesedez, sartu zure pasahitza kontua ezabatu nahi duzula berresteko.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Pasahitza')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-gray-200 px-6 py-2 font-semibold text-gray-800 hover:bg-gray-300 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                        {{ __('Utzi') }}
                    </button>
                </flux:modal.close>

                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-6 py-2 font-semibold text-white hover:opacity-90 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2" data-test="confirm-delete-user-button">
                    {{ __('Kontua ezabatu') }}
                </button>
            </div>
        </form>
    </flux:modal>
</section>
