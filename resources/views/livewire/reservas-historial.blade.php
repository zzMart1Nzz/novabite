<?php

use App\Actions\CancelReservation;
use App\Models\Erreserba;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.public')]
class extends Component {
    private const RESERVATION_DURATION_HOURS = 2;

    public bool $cancelOpen = false;
    public ?int $selectedReservaId = null;
    public string $noticeType = '';
    public string $noticeMessage = '';

    protected function notify(string $type, string $message): void
    {
        $this->noticeType = $type;
        $this->noticeMessage = $message;
        $this->dispatch('nova-toast', type: $type, message: $message);
    }

    protected function telefonoa(): string
    {
        return trim((string) (Auth::user()?->telefonoa ?? ''));
    }

    public function reservationEndsAt(\Carbon\Carbon $startsAt): \Carbon\Carbon
    {
        return $startsAt->copy()->addHours(self::RESERVATION_DURATION_HOURS);
    }

    protected function userReservation(int $reservaId): ?Erreserba
    {
        $telefonoa = $this->telefonoa();
        if ($telefonoa === '') {
            return null;
        }

        return Erreserba::query()
            ->with('mahai')
            ->where('id', $reservaId)
            ->where('telefonoa', $telefonoa)
            ->first();
    }

    public function getProximasReservasProperty()
    {
        $telefonoa = $this->telefonoa();
        if ($telefonoa === '') {
            return collect();
        }

        return Erreserba::query()
            ->with('mahai')
            ->where('telefonoa', $telefonoa)
            ->where('eguna_ordua', '>', now()->subHours(self::RESERVATION_DURATION_HOURS))
            ->orderBy('eguna_ordua')
            ->orderBy('id')
            ->get()
            ->filter(fn ($reserva) => $this->reservationEndsAt($reserva->eguna_ordua)->gt(now()))
            ->values();
    }

    public function getHistorialReservasProperty()
    {
        $telefonoa = $this->telefonoa();
        if ($telefonoa === '') {
            return collect();
        }

        return Erreserba::query()
            ->with('mahai')
            ->where('telefonoa', $telefonoa)
            ->where('eguna_ordua', '<=', now()->subHours(self::RESERVATION_DURATION_HOURS))
            ->orderByDesc('eguna_ordua')
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    public function seleccionarCancelacion(int $reservaId): void
    {
        $reserva = $this->userReservation($reservaId);

        if (! $reserva) {
            $this->notify('error', 'Ez da erreserba hori aurkitu.');
            return;
        }

        if (! $this->reservationEndsAt($reserva->eguna_ordua)->gt(now())) {
            $this->notify('error', 'Amaitutako erreserbak ezin dira ezeztatu.');
            return;
        }

        $this->selectedReservaId = $reservaId;
        $this->cancelOpen = true;
    }

    public function cerrarCancel(): void
    {
        $this->cancelOpen = false;
        $this->selectedReservaId = null;
    }

    public function cancelarSeleccionada(): void
    {
        if (! $this->selectedReservaId) {
            $this->cerrarCancel();
            return;
        }

        $reserva = $this->userReservation($this->selectedReservaId);
        $this->cerrarCancel();

        if (! $reserva) {
            $this->notify('error', 'Ez da erreserba hori aurkitu.');
            return;
        }

        if (! $this->reservationEndsAt($reserva->eguna_ordua)->gt(now())) {
            $this->notify('error', 'Amaitutako erreserbak ezin dira ezeztatu.');
            return;
        }

        try {
            CancelReservation::run($reserva);
            $this->notify('success', 'Erreserba ezeztatuta. Mahaia berriro libre dago.');
        } catch (\Throwable $e) {
            report($e);
            $this->notify('error', 'Errore bat gertatu da ezeztatzean.');
        }
    }
}; ?>

<section class="mx-auto max-w-6xl px-4 py-12 sm:py-16">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold">Nire erreserbak</h1>
            <p class="mt-2 max-w-2xl text-zinc-600">
                Ikusi zure hurrengo erreserbak eta aurreko bisiten historial osoa.
            </p>
        </div>

        <a href="{{ route('reservas', absolute: false) }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-5 py-3 font-semibold text-white hover:bg-[#4F46E5]" wire:navigate>
            Erreserba berria
        </a>
    </div>

    @php
        $alertType = $noticeType ?: (session('success') ? 'success' : (session('error') ? 'error' : ''));
        $alertMessage = $noticeMessage ?: (session('success') ?: session('error'));
    @endphp

    @if ($alertMessage)
        <div class="mt-6 rounded-xl border px-4 py-3 text-sm font-medium {{ $alertType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}" role="status" aria-live="polite">
            {{ $alertMessage }}
        </div>
    @endif

    <div class="mt-10 grid grid-cols-1 gap-8 nb-desktop-two-grid">
        <section>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold">Hurrengo erreserbak</h2>
                <span class="rounded-full bg-[#6366F1]/10 px-3 py-1 text-sm font-semibold text-[#4F46E5]">{{ $this->proximasReservas->count() }}</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($this->proximasReservas as $reserva)
                    @php
                        $amaiera = $this->reservationEndsAt($reserva->eguna_ordua);
                        $martxan = $reserva->eguna_ordua->lte(now()) && $amaiera->gt(now());
                    @endphp

                    <article class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $reserva->eguna_ordua->format('d/m/Y') }}</span>
                                    <span class="rounded-full {{ $martxan ? 'bg-emerald-50 text-emerald-700' : 'bg-[#6366F1]/10 text-[#4F46E5]' }} px-2 py-0.5 text-xs font-bold">
                                        {{ $martxan ? 'Martxan' : 'Hurrengoa' }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-zinc-600">
                                    {{ $reserva->eguna_ordua->format('H:i') }} - {{ $amaiera->format('H:i') }}
                                    · Mahaia {{ $reserva->mahai?->zenbakia ?? $reserva->mahaiak_id }}
                                    · {{ (int) $reserva->pertsona_kopurua }} pertsona
                                </div>
                            </div>

                            <button type="button" wire:click="seleccionarCancelacion({{ $reserva->id }})" class="rounded-xl border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                Ezeztatu
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 bg-white px-4 py-6 text-sm text-zinc-600">
                        Ez duzu hurrengo erreserbarik. Nahi baduzu, egin erreserba berri bat.
                    </div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold">Historiala</h2>
                <span class="rounded-full bg-zinc-100 px-3 py-1 text-sm font-semibold text-zinc-600">{{ $this->historialReservas->count() }}</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($this->historialReservas as $reserva)
                    @php
                        $amaiera = $this->reservationEndsAt($reserva->eguna_ordua);
                    @endphp

                    <article class="rounded-xl border border-zinc-200 bg-white p-4">
                        <div class="flex flex-col gap-1">
                            <div class="font-semibold text-slate-900">{{ $reserva->eguna_ordua->format('d/m/Y') }} · {{ $reserva->eguna_ordua->format('H:i') }} - {{ $amaiera->format('H:i') }}</div>
                            <div class="text-sm text-zinc-600">
                                Mahaia {{ $reserva->mahai?->zenbakia ?? $reserva->mahaiak_id }}
                                · {{ (int) $reserva->pertsona_kopurua }} pertsona
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 bg-white px-4 py-6 text-sm text-zinc-600">
                        Oraindik ez dago aurreko erreserbarik.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @if ($cancelOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarCancel"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-zinc-200 p-6 shadow-xl">
                <h3 class="text-lg font-bold text-red-700">Erreserba ezeztatu</h3>
                <p class="mt-2 text-sm text-zinc-600">
                    Ziur zaude erreserba hau ezeztatu nahi duzula? Mahaia berehala libre agertuko da beste bezeroentzat.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarCancel" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium hover:bg-zinc-50">Ez, mantendu</button>
                    <button type="button" wire:click="cancelarSeleccionada" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:opacity-95">Bai, ezeztatu</button>
                </div>
            </div>
        </div>
    @endif
</section>
