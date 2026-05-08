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
        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
            Toaster::success(__('Profila eguneratuta. Egiaztapen mezua bidali da.'));

            return;
        }

        Toaster::success(__('Profila eguneratuta.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            Toaster::info(__('Posta elektronikoa dagoeneko egiaztatuta dago.'));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
        Toaster::success(__('Egiaztapen mezua berriro bidali da.'));
    }
}; ?>

<section class="w-full">
    <x-settings.layout :heading="__('Ezarpenak')" :subheading="__('Kudeatu zure profila, pasahitza eta kontuaren segurtasuna.')">
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#6366F1] text-lg font-bold text-white">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-zinc-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:items-end">
                    <div class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-sm font-medium {{ auth()->user()->email_verified_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                        {{ auth()->user()->email_verified_at ? __('Posta egiaztatuta') : __('Posta egiaztatu gabe') }}
                    </div>

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <button
                            type="button"
                            wire:click="resendVerificationNotification"
                            wire:loading.attr="disabled"
                            wire:target="resendVerificationNotification"
                            class="inline-flex w-fit items-center justify-center rounded-xl border border-[#6366F1]/30 bg-[#6366F1]/10 px-4 py-2 text-sm font-semibold text-[#4F46E5] transition hover:bg-[#6366F1]/15 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ __('Egiaztapen mezua bidali') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-zinc-200 mb-6">
            <section id="profile" class="scroll-mt-28">
                <div class="mb-6">
                    <flux:heading size="xl" class="mb-2 text-2xl font-bold">{{ __('Profila') }}</flux:heading>
                    <flux:subheading>{{ __('Eguneratu bezero-kontuko izena eta posta elektronikoa.') }}</flux:subheading>
                </div>

                <form wire:submit="updateProfileInformation" class="w-full space-y-6">
                    <flux:input wire:model="name" :label="__('Izena')" type="text" required autofocus autocomplete="name" />

                    <div>
                        <flux:input wire:model="email" :label="__('Posta elektronikoa')" type="email" required autocomplete="email" />

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                <div class="font-semibold">{{ __('Zure posta elektronikoa ez dago egiaztatuta.') }}</div>
                                <button type="button" class="mt-2 font-semibold text-[#4F46E5] hover:underline" wire:click="resendVerificationNotification">
                                    {{ __('Egiaztapen mezua berriro bidali') }}
                                </button>
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

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-zinc-200 mb-6">
            <livewire:settings.password />
        </div>

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-zinc-200 mb-6">
                <livewire:settings.two-factor />
            </div>
        @endif

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-zinc-200">
            <livewire:settings.delete-user-form />
        </div>
    </x-settings.layout>
</section>
