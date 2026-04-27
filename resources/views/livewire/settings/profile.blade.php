<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Toaster::success(__('Profila eguneratuta.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    <x-settings.layout :heading="__('Profila')" :subheading="__('')">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 mb-8">
            <section id="profile" class="scroll-mt-28">
                <form wire:submit="updateProfileInformation" class="w-full space-y-6">
                    <flux:input wire:model="name" :label="__('Izena')" type="text" required autofocus autocomplete="name" />

                    <div>
                        <flux:input wire:model="email" :label="__('Posta elektronikoa')" type="email" required autocomplete="email" />

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                            <div>
                                <flux:text class="mt-4">
                                    {{ __('Zure posta elektronikoa ez dago egiaztatuta.') }}

                                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Sakatu hemen egiaztapen mezua berriro bidaltzeko.') }}
                                    </flux:link>
                                </flux:text>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-end">
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl px-6 py-2 font-semibold text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: #6366F1;" data-test="update-profile-button">
                                {{ __('Gorde') }}
                            </button>
                        </div>

                        <x-action-message class="me-3" on="profile-updated">
                            {{ __('Gordeta.') }}
                        </x-action-message>
                    </div>
                </form>
            </section>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 mb-8">
            <livewire:settings.password />
        </div>

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 mb-8">
                <livewire:settings.two-factor />
            </div>
        @endif

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 mb-8">
            <livewire:settings.delete-user-form />
        </div>
    </x-settings.layout>
</section>
