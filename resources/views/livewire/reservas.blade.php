<?php

use App\Mail\ReservationConfirmedEmail;
use App\Models\Erreserba;
use App\Models\Mahai;
use App\Actions\CancelReservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new 
#[Layout('components.layouts.public')]
class extends Component {
    private const MIN_PERTSONA_KOPURUA = 1;
    private const MAX_PERTSONA_KOPURUA = 8;
    private const RESERVATION_DURATION_HOURS = 2;
    private const SERVICE_WINDOWS = [
        ['label' => 'Bazkaria', 'first_start' => '12:30', 'last_start' => '14:30'],
        ['label' => 'Afaria', 'first_start' => '19:30', 'last_start' => '21:30'],
    ];

    public string $data = '';
    public string $ordua = '';
    public $pertsonaKopurua = 2;
    public string $bezeroIzena = '';
    public string $telefonoa = '';
    public bool $featureEnabled = false;

    public bool $confirmOpen = false;
    public ?int $selectedMahaiId = null;

    public bool $cancelOpen = false;
    public ?int $selectedReservaId = null;

    public string $noticeType = '';
    public string $noticeMessage = '';

    public function mount(): void
    {
        $this->featureEnabled = Schema::hasTable('mahaiak') && Schema::hasTable('erreserbak');
        $this->data = now()->toDateString();

        $user = Auth::user();
        if ($user) {
            $this->bezeroIzena = (string) ($user->name ?? '');
            $this->telefonoa = (string) ($user->telefonoa ?? '');
        }

        $this->syncOrdua(false);
    }

    protected function notify(string $type, string $message): void
    {
        $this->noticeType = $type;
        $this->noticeMessage = $message;
        $this->dispatch('nova-toast', type: $type, message: $message);
    }

    public function checkTimeRestriction()
    {
        if (! $this->featureEnabled) {
            return;
        }

        if ($this->ordua === '') {
            $this->syncOrdua(false);
            return;
        }

        if (! $this->selectedOrduaIsAvailable()) {
            $this->clearSelectedMahai();
            $this->syncOrdua(false);
            $this->notify('info', 'Aukeratutako ordua jada ez dago erabilgarri; ordutegia eguneratu da.');
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'data') {
            $this->clearSelectedMahai();
            $this->syncOrdua(false);
        }

        if ($property === 'ordua') {
            $this->clearSelectedMahai();
        }

        if ($property === 'pertsonaKopurua') {
            $this->sanitizePertsonaKopurua();
            $this->clearSelectedMahai();
        }
    }

    protected function clearSelectedMahai(): void
    {
        $this->confirmOpen = false;
        $this->selectedMahaiId = null;
    }

    protected function sanitizePertsonaKopurua(): int
    {
        $raw = $this->pertsonaKopurua;

        if ($raw === null || trim((string) $raw) === '' || ! is_numeric($raw)) {
            $this->pertsonaKopurua = self::MIN_PERTSONA_KOPURUA;
            return self::MIN_PERTSONA_KOPURUA;
        }

        $value = (int) $raw;
        $value = max(self::MIN_PERTSONA_KOPURUA, min(self::MAX_PERTSONA_KOPURUA, $value));
        $this->pertsonaKopurua = $value;

        return $value;
    }

    protected function syncOrdua(bool $preferCurrent = true): void
    {
        $auk = $this->orduaAukerak;
        if (! $auk) {
            $this->ordua = '';
            return;
        }

        if (! $preferCurrent || $this->ordua === '' || ! in_array($this->ordua, $auk, true)) {
            $this->ordua = (string) $auk[0];
        }
    }

    protected function selectedOrduaIsAvailable(): bool
    {
        return $this->ordua !== '' && in_array($this->ordua, $this->orduaAukerak, true);
    }

    protected function serviceWindowsForDay(\Carbon\Carbon $day): array
    {
        return array_map(function (array $window) use ($day) {
            $firstStart = \Carbon\Carbon::parse($day->toDateString().' '.$window['first_start'].':00');
            $lastStart = \Carbon\Carbon::parse($day->toDateString().' '.$window['last_start'].':00');

            return [
                'label' => $window['label'],
                'first_start' => $firstStart,
                'last_start' => $lastStart,
                'service_end' => $lastStart->copy()->addHours(self::RESERVATION_DURATION_HOURS),
            ];
        }, self::SERVICE_WINDOWS);
    }

    protected function txandaFor(\Carbon\Carbon $startsAt): ?array
    {
        foreach ($this->serviceWindowsForDay($startsAt->copy()->startOfDay()) as $window) {
            $cursor = $window['first_start']->copy();

            while ($cursor->lte($window['last_start'])) {
                if ($startsAt->format('Y-m-d H:i:s') === $cursor->format('Y-m-d H:i:s')) {
                    return $window;
                }

                $cursor->addMinutes(30);
            }
        }

        return null;
    }

    protected function isAllowedReservationStart(\Carbon\Carbon $startsAt): bool
    {
        return $this->txandaFor($startsAt) !== null;
    }

    public function getOrduaAukerakProperty(): array
    {
        if (! $this->featureEnabled) {
            return [];
        }

        $date = $this->data ?: now()->toDateString();
        $day = \Carbon\Carbon::parse($date)->startOfDay();
        $isToday = $day->isToday();
        $windows = $this->serviceWindowsForDay($day);

        $rounded = null;
        if ($isToday) {
            $cutoff = now()->copy()->addHour();
            $rounded = $cutoff->copy()->second(0);
            $minutes = (int) $rounded->format('i');
            $delta = (30 - ($minutes % 30)) % 30;
            $rounded->addMinutes($delta);
        }

        $times = [];
        foreach ($windows as $window) {
            $min = $window['first_start']->copy();
            $lastStart = $window['last_start']->copy();
            if ($rounded && $rounded->gt($min)) {
                $min = $rounded->copy();
            }

            if ($min->gt($lastStart)) {
                continue;
            }

            $cursor = $min->copy();
            $cursor->second = 0;
            while ($cursor->lte($lastStart)) {
                $times[] = $cursor->format('H:i');
                $cursor->addMinutes(30);
            }
        }

        return array_values(array_unique($times));
    }

    protected function getEgunaOrdua(): ?\Carbon\Carbon
    {
        if ($this->ordua === '') {
            return null;
        }

        $date = $this->data ?: now()->toDateString();
        try {
            return \Carbon\Carbon::parse($date.' '.$this->ordua.':00');
        } catch (\Throwable) {
            return null;
        }
    }

    public function reservationEndsAt(\Carbon\Carbon $startsAt): \Carbon\Carbon
    {
        return $startsAt->copy()->addHours(self::RESERVATION_DURATION_HOURS);
    }

    public function getAmaieraOrduaProperty(): string
    {
        $startsAt = $this->getEgunaOrdua();

        return $startsAt ? $this->reservationEndsAt($startsAt)->format('H:i') : '';
    }

    public function getTxandaProperty(): string
    {
        $startsAt = $this->getEgunaOrdua();

        return $startsAt ? (string) ($this->txandaFor($startsAt)['label'] ?? '') : '';
    }

    protected function reservationConflictBounds(\Carbon\Carbon $startsAt): array
    {
        return [
            $startsAt->copy()->subHours(self::RESERVATION_DURATION_HOURS),
            $this->reservationEndsAt($startsAt),
        ];
    }

    protected function conflictingReservationsQuery(?int $mahaiId, \Carbon\Carbon $startsAt)
    {
        [$from, $to] = $this->reservationConflictBounds($startsAt);

        $query = Erreserba::query()
            ->where('eguna_ordua', '>', $from)
            ->where('eguna_ordua', '<', $to);

        if ($mahaiId !== null) {
            $query->where('mahaiak_id', $mahaiId);
        }

        return $query;
    }

    public function getSelectedMahaiProperty(): ?Mahai
    {
        if (! $this->featureEnabled) {
            return null;
        }

        if (! $this->selectedMahaiId) return null;
        return Mahai::query()->find($this->selectedMahaiId);
    }

    public function getMahaiakProperty()
    {
        if (! $this->featureEnabled) {
            return collect();
        }

        return Mahai::query()
            ->whereRaw('LOWER(COALESCE(kokapena, "")) NOT IN (?, ?)', ['barra', 'ekitaldi'])
            ->where(function ($query) {
                $query->whereNull('zenbakia')->orWhere('zenbakia', '!=', 8);
            })
            ->orderBy('id')
            ->get();
    }

    protected function reservableOnline(Mahai $mahai): bool
    {
        $kokapena = mb_strtolower((string) ($mahai->kokapena ?? ''));
        $zenbakia = (int) ($mahai->zenbakia ?? 0);

        return ! in_array($kokapena, ['barra', 'ekitaldi'], true) && $zenbakia !== 8;
    }

    protected function terrazaIrekita(\Carbon\Carbon $when): bool
    {
        $reservationStart = $when->copy()->minute(0)->second(0);
        $reservationEnd = $when->copy()->addHours(2)->minute(0)->second(0);
        $daysAhead = (int) now('Europe/Madrid')->startOfDay()->diffInDays($when->copy()->startOfDay(), false);
        $forecastDays = max(1, min(16, $daysAhead + 1));
        $cacheKey = 'meteo:ordizia:forecast:'.$when->format('Ymd').':'.$forecastDays.':v2';

        $data = Cache::remember($cacheKey, 600, function () use ($forecastDays) {
            $url = 'https://api.open-meteo.com/v1/forecast';
            $query = [
                'latitude' => 43.05,
                'longitude' => -2.18,
                'hourly' => 'precipitation,precipitation_probability,weather_code',
                'timezone' => 'Europe/Madrid',
                'forecast_days' => $forecastDays,
            ];

            $res = Http::timeout(5)->retry(1, 200)->get($url, $query);
            if (! $res->ok()) {
                return null;
            }

            return $res->json();
        });

        if (! is_array($data)) {
            return true;
        }

        $times = (array) data_get($data, 'hourly.time', []);
        $prec = (array) data_get($data, 'hourly.precipitation', []);
        $prob = (array) data_get($data, 'hourly.precipitation_probability', []);
        $codes = (array) data_get($data, 'hourly.weather_code', []);
        $rainCodes = [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82, 95, 96, 99];

        $cursor = $reservationStart->copy();
        while ($cursor->lte($reservationEnd)) {
            $idx = array_search($cursor->format('Y-m-d\TH:i'), $times, true);
            $cursor->addHour();

            if ($idx === false) {
                continue;
            }

            $precMm = (float) ($prec[$idx] ?? 0);
            $probPct = (int) ($prob[$idx] ?? 0);
            $weatherCode = (int) ($codes[$idx] ?? 0);

            if ($precMm > 0.1 || $probPct >= 50 || in_array($weatherCode, $rainCodes, true)) {
                return false;
            }
        }

        return true;
    }

    public function getTerrazaDisponibleProperty(): bool
    {
        $when = $this->getEgunaOrdua();
        if (! $when) {
            return true;
        }

        return $this->terrazaIrekita($when);
    }

    public function getOcupadasProperty(): array
    {
        if (! $this->featureEnabled) {
            return [];
        }

        $egunaOrdua = $this->getEgunaOrdua();
        if (! $egunaOrdua) {
            return [];
        }

        $myPhone = $this->telefonoa !== '' ? $this->telefonoa : (string) (auth()->user()->telefonoa ?? '');

        $rows = $this->conflictingReservationsQuery(null, $egunaOrdua)
            ->select(['mahaiak_id', 'telefonoa'])
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $tableId = (int) ($row->mahaiak_id ?? 0);
            $phone = (string) ($row->telefonoa ?? '');
            if ($tableId <= 0 || $phone === '') {
                continue;
            }

            if (! array_key_exists($tableId, $out)) {
                $out[$tableId] = $phone;
            }

            if ($myPhone !== '' && $phone === $myPhone) {
                $out[$tableId] = $myPhone;
            }
        }

        return $out;
    }

    public function getMisReservasProperty()
    {
        if (! $this->featureEnabled) return collect();
        if (! auth()->check()) return collect();

        $telefonoa = (string) (auth()->user()->telefonoa ?? '');
        if ($telefonoa === '') {
            return collect();
        }

        return Erreserba::query()
            ->with('mahai')
            ->where('telefonoa', $telefonoa)
            ->where('eguna_ordua', '>', now()->subHours(self::RESERVATION_DURATION_HOURS))
            ->orderBy('eguna_ordua')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->filter(fn ($r) => $this->reservationEndsAt($r->eguna_ordua)->gt(now()))
            ->values();
    }

    public function seleccionarMahaia(int $mahaiId): void
    {
        if (! $this->featureEnabled) {
            $this->notify('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return;
        }

        if (! auth()->check()) {
            $this->redirect('/login', navigate: true);
            return;
        }

        $user = auth()->user();
        if (! $user || (string) ($user->telefonoa ?? '') === '') {
            $this->notify('error', 'Ezin da erreserbatu: ez duzu telefonoa ezarrita.');
            return;
        }

        if (! $this->selectedOrduaIsAvailable()) {
            $this->notify('error', 'Aukeratutako ordua ez dago erabilgarri. Aukeratu berriro.');
            return;
        }

        $egunaOrdua = $this->getEgunaOrdua();
        if (! $egunaOrdua) {
            $this->notify('error', 'Aukeratu ordua.');
            return;
        }

        if (! $this->isAllowedReservationStart($egunaOrdua)) {
            $this->notify('error', 'Aukeratutako ordua ez dago bazkari edo afari txanden barruan.');
            return;
        }

        $pertsonaKopurua = $this->sanitizePertsonaKopurua();
        if ($pertsonaKopurua < self::MIN_PERTSONA_KOPURUA || $pertsonaKopurua > self::MAX_PERTSONA_KOPURUA) {
            $this->notify('error', 'Pertsona kopurua ezin da 1 baino txikiagoa izan.');
            return;
        }

        $mahai = Mahai::query()->find($mahaiId);
        if (! $mahai) {
            $this->notify('error', 'Ez da mahaia aurkitu.');
            return;
        }

        if (! $this->reservableOnline($mahai)) {
            $this->notify('error', 'Mahaia ez dago web bidez erreserbatzeko erabilgarri.');
            return;
        }

        if (mb_strtolower((string) ($mahai->kokapena ?? '')) === 'terraza' && ! $this->terrazaIrekita($egunaOrdua)) {
            $this->notify('error', 'Terraza itxita dago eguraldiagatik.');
            return;
        }

        $cap = (int) ($mahai->pertsona_kopurua ?? 0);
        if ($cap > 0 && $cap < $pertsonaKopurua) {
            $this->notify('error', 'Mahaia txikiegia da aukeratutako pertsona kopururako.');
            return;
        }

        $this->selectedMahaiId = $mahaiId;
        $this->confirmOpen = true;
    }

    public function cerrarConfirm(): void
    {
        $this->confirmOpen = false;
        $this->selectedMahaiId = null;
    }

    public function confirmarReserva()
    {
        if (! $this->selectedMahaiId) return;

        $id = $this->selectedMahaiId;
        $this->cerrarConfirm();
        return $this->reservar($id);
    }

    public function seleccionarCancelacion(int $reservaId): void
    {
        $this->selectedReservaId = $reservaId;
        $this->cancelOpen = true;
    }

    public function cerrarCancel(): void
    {
        $this->cancelOpen = false;
        $this->selectedReservaId = null;
    }

    public function cancelarSeleccionada()
    {
        if (! $this->featureEnabled) {
            $this->notify('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return;
        }

        $user = Auth::user();
        if (! $user || ! $this->selectedReservaId) {
            $this->cerrarCancel();
            return;
        }

        $telefonoa = $this->telefonoa !== '' ? $this->telefonoa : (string) ($user->telefonoa ?? '');
        if ($telefonoa === '') {
            $this->notify('error', 'Ezin da ezeztatu: ez duzu telefonoa ezarrita.');
            return;
        }

        $reservaId = $this->selectedReservaId;
        $this->cerrarCancel();

        try {
            $r = Erreserba::query()
                ->where('id', $reservaId)
                ->where('telefonoa', $telefonoa)
                ->first();

            if (! $r) {
                $this->notify('error', 'Ez da erreserba hori aurkitu.');
                return;
            }

            if (! $this->reservationEndsAt($r->eguna_ordua)->gt(now())) {
                $this->notify('error', 'Amaitutako erreserbak ezin dira ezeztatu.');
                return;
            }

            CancelReservation::run($r);

            $this->notify('success', 'Erreserba ezeztatuta.');
            return;
        } catch (\Throwable $e) {
            report($e);
            $this->notify('error', 'Errore bat gertatu da ezeztatzean.');
            return;
        }
    }

    public function reservar(int $mahaiId)
    {
        if (! $this->featureEnabled) {
            $this->notify('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return;
        }

        $user = Auth::user();
        if (! $user) {
            $this->redirect('/login', navigate: true);
            return;
        }

        if (! $this->selectedOrduaIsAvailable()) {
            $this->notify('error', 'Aukeratutako ordua ez dago erabilgarri. Aukeratu berriro.');
            return;
        }

        $egunaOrdua = $this->getEgunaOrdua();
        if (! $egunaOrdua) {
            $this->notify('error', 'Aukeratu ordua.');
            return;
        }

        if (! $this->isAllowedReservationStart($egunaOrdua)) {
            $this->notify('error', 'Aukeratutako ordua ez dago bazkari edo afari txanden barruan.');
            return;
        }

        if ($egunaOrdua->lt(now())) {
            $this->notify('error', 'Ezin da iraganeko ordu batean erreserbatu.');
            return;
        }

        if ($egunaOrdua->lt(now()->addHour())) {
            $this->notify('error', 'Erreserbatzeko, gutxienez 1 orduko aurrerapena behar da.');
            return;
        }

        $bezeroIzena = trim((string) ($user->name ?? $this->bezeroIzena));
        $telefonoa = trim((string) ($user->telefonoa ?? $this->telefonoa));
        if ($bezeroIzena === '' || $telefonoa === '') {
            $this->notify('error', 'Ezin da erreserbatu: falta dira zure datuak (izena edo telefonoa).');
            return;
        }

        $pertsonaKopurua = $this->sanitizePertsonaKopurua();
        if ($pertsonaKopurua < self::MIN_PERTSONA_KOPURUA || $pertsonaKopurua > self::MAX_PERTSONA_KOPURUA) {
            $this->notify('error', 'Pertsona kopurua 1 eta 8 artekoa izan behar da.');
            return;
        }

        $lockKey = 'erreserbak:'.$mahaiId.':'.$egunaOrdua->format('Ymd');
        $lockAcquired = false;
        $committed = false;

        try {
            if (DB::getDriverName() === 'mysql') {
                $row = DB::selectOne('SELECT GET_LOCK(?, ?) AS l', [$lockKey, 10]);
                $lockAcquired = (int) ($row->l ?? 0) === 1;

                if (! $lockAcquired) {
                    $this->notify('error', 'Beste bezero bat erreserba baieztatzen ari da. Saiatu berriro segundo gutxi barru.');
                    return;
                }
            }

            // Set isolation level to SERIALIZABLE to avoid race conditions
            DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
            DB::beginTransaction();

            $yaOcupada = $this->conflictingReservationsQuery($mahaiId, $egunaOrdua)->exists();

            if ($yaOcupada) {
                DB::rollBack();
                $this->notify('error', 'Mahaia erreserbatu berri dute aukeratutako ordurako. Aukeratu beste mahai libre bat.');
                return;
            }

            $mahai = Mahai::query()->find($mahaiId);
            if (! $mahai) {
                DB::rollBack();
                $this->notify('error', 'Ez da mahaia aurkitu.');
                return;
            }

            if (! $this->reservableOnline($mahai)) {
                DB::rollBack();
                $this->notify('error', 'Mahaia ez dago web bidez erreserbatzeko erabilgarri.');
                return;
            }

            if (mb_strtolower((string) ($mahai->kokapena ?? '')) === 'terraza' && ! $this->terrazaIrekita($egunaOrdua)) {
                DB::rollBack();
                $this->notify('error', 'Terraza itxita dago eguraldiagatik.');
                return;
            }

            $cap = (int) ($mahai->pertsona_kopurua ?? 0);
            if ($cap > 0 && $cap < $pertsonaKopurua) {
                DB::rollBack();
                $this->notify('error', 'Mahaia txikiegia da aukeratutako pertsona kopururako.');
                return;
            }

            $langileId = (int) (DB::table('langileak')->where('ezabatua', 0)->min('id') ?? DB::table('langileak')->min('id') ?? 1);

            $erreserba = Erreserba::create([
                'bezero_izena' => $bezeroIzena,
                'telefonoa' => $telefonoa,
                'pertsona_kopurua' => $pertsonaKopurua,
                'eguna_ordua' => $egunaOrdua,
                'prezio_totala' => 0,
                'ordainduta' => false,
                'faktura_ruta' => null,
                'langileak_id' => $langileId,
                'mahaiak_id' => $mahaiId,
            ]);

            // Cargamos relaciones para el correo
            $erreserba->load(['mahai']);

            DB::commit();
            $committed = true;

            $emailSent = false;
            try {
                $reservasUrl = rtrim((string) config('app.url'), '/').route('reservas.historial', absolute: false);

                Mail::to($user->email)->send(new ReservationConfirmedEmail($erreserba, $reservasUrl));
                $emailSent = true;
            } catch (\Throwable $mailError) {
                report($mailError);
            }

            $this->notify('success', $emailSent ? 'Erreserba baieztatuta! Mezu bat bidali da.' : 'Erreserba baieztatuta!');
            return;
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            report($e);
            $this->notify('error', 'Errore bat gertatu da erreserbatzean.');
            return;
        } finally {
            if (DB::getDriverName() === 'mysql' && $lockAcquired) {
                DB::select('SELECT RELEASE_LOCK(?)', [$lockKey]);
            }
        }
    }
}; ?>

<div>
    @if ($featureEnabled)
        <div wire:poll.10s="checkTimeRestriction"></div>

        <section class="mx-auto max-w-6xl px-4 py-12 sm:py-16">
            <h1 class="text-3xl sm:text-4xl font-extrabold">Mahaia erreserbatu</h1>
            <p class="mt-2 text-zinc-600">
                Aukeratu eguna, ordua eta pertsonak; libre dagoen mahaia hautatu, eta azken leihoan baieztatu.
            </p>

            @php
                $alertType = $noticeType ?: (session('success') ? 'success' : (session('error') ? 'error' : ''));
                $alertMessage = $noticeMessage ?: (session('success') ?: session('error'));
            @endphp

            @if ($alertMessage)
                <div class="mt-6 rounded-xl border px-4 py-3 text-sm font-medium {{ $alertType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-700' }}" role="status" aria-live="polite">
                    {{ $alertMessage }}
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-[#6366F1]/30 bg-[#6366F1]/10 px-4 py-3">
                    <div class="text-xs font-bold uppercase text-[#4F46E5]">1. pausoa</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">Data eta pertsonak</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white px-4 py-3">
                    <div class="text-xs font-bold uppercase text-zinc-500">2. pausoa</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">Mahaia aukeratu</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white px-4 py-3">
                    <div class="text-xs font-bold uppercase text-zinc-500">3. pausoa</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">Baieztatu</div>
                </div>
            </div>

        <div class="mt-8 grid grid-cols-1 nb-desktop-reservas-grid gap-6">
            <div class="rounded-2xl border border-zinc-200 p-6">
                <h2 class="font-semibold">1. Aukeratu data, ordua eta pertsonak</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Eguna</label>
                        <p class="mt-1 text-xs text-zinc-500">Egutegian aukeratu</p>
                        <div class="mt-3 rounded-2xl border border-zinc-200 bg-white p-3" wire:ignore>
                            <input
                                type="text"
                                inputmode="none"
                                data-sush-date
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#6366F1]"
                                wire:model.live="data"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Ordua</label>
                        <p class="mt-1 text-xs text-zinc-500">Bazkarian eta afarian erreserbatu daiteke, gutxienez 1 orduko aurrerapenarekin.</p>
                        @if (count($this->orduaAukerak) === 0)
                            <div class="mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                Ez dago bazkari edo afari ordurik erabilgarri aukeratutako egunerako.
                            </div>
                        @else
                            <select
                                wire:key="ordua-select-{{ $data }}"
                                wire:model.live="ordua"
                                class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#6366F1]"
                            >
                                @foreach ($this->orduaAukerak as $o)
                                    <option value="{{ $o }}" @selected($ordua === $o)>{{ $o }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Pertsona kopurua</label>
                        <select
                            wire:model.live="pertsonaKopurua"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#6366F1]"
                        >
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="border-l-4 border-[#6366F1] bg-[#6366F1]/5 px-4 py-3 text-sm">
                        <div class="font-semibold text-slate-900">Zure aukeraketa</div>
                        <div class="mt-1 text-zinc-600">
                            {{ \Carbon\Carbon::parse($data ?: now()->toDateString())->format('d/m/Y') }}
                            @if ($this->txanda)
                                · {{ $this->txanda }}
                            @endif
                            · {{ $ordua ?: '—' }}{{ $this->amaieraOrdua ? ' - '.$this->amaieraOrdua : '' }}
                            · {{ (int) $pertsonaKopurua }} pertsona
                        </div>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200">
                        <div class="text-sm font-medium">Koloreen legenda</div>
                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded" style="background-color: #2EAD6B;"></span>
                                Libre
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded" style="background-color: #6366F1;"></span>
                                Zure erreserba
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded" style="background-color: #D94B5A;"></span>
                                Okupatuta
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nb-desktop-span-2 rounded-2xl border border-zinc-200 p-6">
                <h2 class="font-semibold">2. Aukeratu mahaia</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    Data: <strong>{{ \Carbon\Carbon::parse($data ?: now()->toDateString())->format('d/m/Y') }}</strong> ·
                    Txanda: <strong>{{ $this->txanda ?: '—' }}</strong> ·
                    Ordua: <strong>{{ $ordua ?: '—' }}{{ $this->amaieraOrdua ? ' - '.$this->amaieraOrdua : '' }}</strong> ·
                    Pertsonak: <strong>{{ (int) $pertsonaKopurua }}</strong>
                </p>

                @php
                    $requiredPeople = max(1, min(8, (int) $pertsonaKopurua));
                    $myPhone = (string) (auth()->user()?->telefonoa ?? '');
                    $terrazaOn = $this->terrazaDisponible;
                    $allMahaiak = $this->mahaiak;
                    $ocupadas = $this->ocupadas;

                    $isAvailable = function($mahai) use ($requiredPeople, $terrazaOn, $ocupadas) {
                        if (! $mahai) return;

                        $kokapena = mb_strtolower((string) ($mahai->kokapena ?? ''));
                        $cap = (int) ($mahai->pertsona_kopurua ?? 0);
                        $txikiegia = $cap > 0 && $cap < $requiredPeople;
                        $terrazaItxita = $kokapena === 'terraza' && ! $terrazaOn;

                        return ! array_key_exists($mahai->id, $ocupadas) && ! $txikiegia && ! $terrazaItxita;
                    };

                    $availableCount = $allMahaiak->filter($isAvailable)->count();

                    $renderBtn = function($mahai) use ($requiredPeople, $myPhone, $terrazaOn, $ocupadas) {
                        if (! $mahai) return;

                        $kokapena = mb_strtolower((string) ($mahai->kokapena ?? ''));
                        $ocupadaTelefonoa = $ocupadas[$mahai->id] ?? null;
                        $ocupada = $ocupadaTelefonoa !== null;
                        $esMia = $ocupada && $ocupadaTelefonoa === ($myPhone !== '' ? $myPhone : (auth()->user()?->telefonoa ?? null));

                        $cap = (int) ($mahai->pertsona_kopurua ?? 0);
                        $txikiegia = $cap > 0 && $cap < $requiredPeople;
                        $terrazaItxita = $kokapena === 'terraza' && ! $terrazaOn;

                        $bgColor = '#2EAD6B';
                        $status = 'Libre';
                        if ($ocupada) {
                            $bgColor = $esMia ? '#6366F1' : '#D94B5A';
                            $status = $esMia ? 'Zure erreserba' : 'Okupatuta';
                        } elseif ($txikiegia || $terrazaItxita) {
                            $bgColor = '#94A3B8';
                            $status = $terrazaItxita ? 'Itxita' : 'Txikiegia';
                        }

                        $disabled = ($ocupada || $txikiegia || $terrazaItxita) ? 'disabled' : '';
                        $wireClick = ($disabled === '') ? 'wire:click="seleccionarMahaia('.$mahai->id.')"' : '';

                        echo '<button type="button"
                            class="relative rounded-2xl p-5 text-left text-white transition hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6366F1] disabled:cursor-not-allowed disabled:opacity-90 disabled:hover:scale-100 disabled:hover:shadow-none cursor-pointer flex flex-col justify-between"
                            style="background-color: ' . $bgColor . ';"
                            ' . $wireClick . '
                            ' . $disabled . '
                        >
                            <div class="text-lg font-bold">Mahaia ' . ($mahai->zenbakia ?? $mahai->id) . '</div>
                            <div class="text-xs opacity-90">
                                ' . ($cap ? ('Kapazitatea: ' . $cap) : '') . '
                            </div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-wide opacity-95">' . e($status) . '</div>
                        </button>';
                    };

                    $interior = $allMahaiak->filter(fn ($m) => mb_strtolower((string) ($m->kokapena ?? '')) === 'interior');
                    $terraza = $allMahaiak->filter(fn ($m) => mb_strtolower((string) ($m->kokapena ?? '')) === 'terraza');
                @endphp

                @if ($availableCount === 0)
                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Ez dago mahairik libre aukeratutako ordu eta pertsona kopururako. Probatu beste ordu batekin edo pertsona kopuru txikiagoarekin.
                    </div>
                @endif

                <div class="mt-6 space-y-8">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Interior</div>
                        @if ($interior->isEmpty())
                            <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-700">
                                Ez dago barruko mahairik erabilgarri web bidez.
                            </div>
                        @else
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 nb-desktop-table-grid gap-4">
                                @foreach ($interior as $m)
                                    {!! $renderBtn($m) !!}
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-zinc-900">Terraza</div>
                            @if (! $this->terrazaDisponible)
                                <div class="text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2 py-1 rounded-lg">
                                    Itxita eguraldiagatik
                                </div>
                            @endif
                        </div>

                        @if ($this->terrazaDisponible)
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 nb-desktop-table-grid gap-4">
                                @foreach ($terraza as $m)
                                    {!! $renderBtn($m) !!}
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-700">
                                Terraza ez dago erabilgarri aukeratutako ordurako.
                            </div>
                        @endif
                    </div>

                </div>

                <div class="mt-8 rounded-xl bg-zinc-50 p-4 border border-zinc-200">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-medium">Zure hurrengo erreserbak</div>
                        <a href="{{ route('reservas.historial', absolute: false) }}" class="text-sm font-semibold text-[#6366F1] hover:underline" wire:navigate>
                            Historiala ikusi
                        </a>
                    </div>
                    <div class="mt-3 space-y-2 text-sm">
                        @forelse ($this->misReservas as $r)
                            @php
                                $amaiera = $this->reservationEndsAt($r->eguna_ordua);
                                $esFutura = $amaiera->gt(now());
                                $puedeCancelar = $esFutura;
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2">
                                <div>
                                    <div>
                                        <span class="font-semibold">{{ $r->eguna_ordua->format('d/m/Y H:i') }} - {{ $amaiera->format('H:i') }}</span>
                                        · Mahaia {{ $r->mahai?->zenbakia ?? $r->mahaiak_id }}
                                        · {{ (int) $r->pertsona_kopurua }} pertsona
                                    </div>
                                    <div class="text-xs text-zinc-500">Erreserba #{{ $r->id }} · {{ $esFutura ? 'Hurrengoa' : 'Iragana' }}</div>
                                </div>

                                @if ($puedeCancelar)
                                    <button
                                        type="button"
                                        wire:click="seleccionarCancelacion({{ $r->id }})"
                                        class="rounded-xl border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                                    >
                                        Ezeztatu
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="text-zinc-500">Ez duzu hurrengo erreserbarik.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        </section>

    <!-- Modal confirmar reserva -->
    @if ($confirmOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarConfirm"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-zinc-200 p-6 shadow-xl">
                <h3 class="text-lg font-bold">3. Erreserba baieztatu</h3>
                <p class="mt-2 text-sm text-zinc-600">
                    Erreserbatuko duzu: <strong>Mahaia {{ $this->selectedMahai?->zenbakia ?? ($selectedMahaiId ?? '') }}</strong>
                    egunerako: <strong>{{ \Carbon\Carbon::parse($data ?: now()->toDateString())->format('d/m/Y') }}</strong> ·
                    txanda: <strong>{{ $this->txanda ?: '—' }}</strong> ·
                    ordua: <strong>{{ $ordua ?: '—' }}{{ $this->amaieraOrdua ? ' - '.$this->amaieraOrdua : '' }}</strong> ·
                    pertsonak: <strong>{{ (int) $pertsonaKopurua }}</strong>
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button type="button" wire:click="cerrarConfirm" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium hover:bg-zinc-50" wire:loading.attr="disabled" wire:target="confirmarReserva">Ezeztatu</button>
                    <button type="button" wire:click="confirmarReserva" class="rounded-xl bg-[#6366F1] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4F46E5] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2" wire:loading.attr="disabled" wire:target="confirmarReserva">
                        <svg wire:loading wire:target="confirmarReserva" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Baieztatu</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal cancelar reserva -->
    @if ($cancelOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/50" wire:click="cerrarCancel"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-zinc-200 p-6 shadow-xl">
                <h3 class="text-lg font-bold text-red-700">Erreserba ezeztatu</h3>
                <p class="mt-2 text-sm text-zinc-600">
                    Ziur zaude erreserba hau ezeztatu nahi duzula? Mahaia berehala libre agertuko da beste bezeroentzat.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button type="button" wire:click="cerrarCancel" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium hover:bg-zinc-50">Ez, mantendu</button>
                    <button type="button" wire:click="cancelarSeleccionada" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:opacity-95">Bai, ezeztatu</button>
                </div>
            </div>
        </div>
    @endif
    @else
        <section class="mx-auto max-w-6xl px-4 py-12 sm:py-16">
            <h1 class="text-3xl sm:text-4xl font-extrabold">Erreserbak</h1>
            <p class="mt-2 text-zinc-600">
                Erreserben modulua ez dago aktibo une honetan.
            </p>
        </section>
    @endif
</div>
