<?php

use App\Mail\ReservationConfirmedEmail;
use App\Models\Erreserba;
use App\Models\Mahai;
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
    public string $data = '';
    public string $ordua = '';
    public int $pertsonaKopurua = 2;
    public string $bezeroIzena = '';
    public string $telefonoa = '';
    public bool $featureEnabled = false;

    public bool $confirmOpen = false;
    public ?int $selectedMahaiId = null;

    public bool $cancelOpen = false;
    public ?int $selectedReservaId = null;

    public function mount(): void
    {
        $this->featureEnabled = Schema::hasTable('mahaiak') && Schema::hasTable('erreserbak');
        $this->data = now()->toDateString();

        $user = Auth::user();
        if ($user) {
            $this->bezeroIzena = (string) ($user->name ?? '');
            $this->telefonoa = (string) ($user->telefonoa ?? '');
        }

        $this->syncOrdua();
    }

    public function checkTimeRestriction()
    {
        if (! $this->featureEnabled) {
            return;
        }

        $this->syncOrdua();
    }

    public function updated(string $property): void
    {
        if ($property === 'data') {
            $this->syncOrdua();
        }

        if ($property === 'pertsonaKopurua' && $this->pertsonaKopurua < 1) {
            $this->pertsonaKopurua = 1;
        }
    }

    protected function syncOrdua(): void
    {
        $auk = $this->orduaAukerak;
        if (! $auk) {
            $this->ordua = '';
            return;
        }

        if ($this->ordua === '' || ! in_array($this->ordua, $auk, true)) {
            $this->ordua = (string) $auk[0];
        }
    }

    public function getOrduaAukerakProperty(): array
    {
        if (! $this->featureEnabled) {
            return [];
        }

        $date = $this->data ?: now()->toDateString();
        $day = \Carbon\Carbon::parse($date)->startOfDay();
        $isToday = $day->isToday();
        $windows = [
            [$day->copy()->setTime(12, 30), $day->copy()->setTime(14, 30)],
            [$day->copy()->setTime(19, 30), $day->copy()->setTime(21, 30)],
        ];

        $rounded = null;
        if ($isToday) {
            $cutoff = now()->copy()->addHour();
            $rounded = $cutoff->copy()->second(0);
            $minutes = (int) $rounded->format('i');
            $delta = (30 - ($minutes % 30)) % 30;
            $rounded->addMinutes($delta);
        }

        $times = [];
        foreach ($windows as [$start, $end]) {
            $min = $start->copy();
            if ($rounded && $rounded->gt($min)) {
                $min = $rounded->copy();
            }

            if ($min->gt($end)) {
                continue;
            }

            $cursor = $min->copy();
            $cursor->second = 0;
            while ($cursor->lte($end)) {
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
            ->whereRaw('LOWER(COALESCE(kokapena, "")) != ?', ['barra'])
            ->orderBy('id')
            ->get();
    }

    protected function terrazaIrekita(\Carbon\Carbon $when): bool
    {
        $whenHour = $when->copy()->minute(0)->second(0);
        $cacheKey = 'meteo:ordizia:'.$whenHour->format('YmdH');

        $data = Cache::remember($cacheKey, 600, function () {
            $url = 'https://api.open-meteo.com/v1/forecast';
            $query = [
                'latitude' => 43.05,
                'longitude' => -2.18,
                'hourly' => 'precipitation,precipitation_probability',
                'timezone' => 'Europe/Madrid',
                'forecast_days' => 3,
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

        $target = $whenHour->format('Y-m-d\TH:i');
        $idx = array_search($target, $times, true);
        if ($idx === false) {
            return true;
        }

        $precMm = (float) ($prec[$idx] ?? 0);
        $probPct = (int) ($prob[$idx] ?? 0);

        return $precMm <= 0.1 && $probPct < 50;
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

        $from = $egunaOrdua->copy()->subHours(2);
        $to = $egunaOrdua->copy()->addHours(2);
        $myPhone = $this->telefonoa !== '' ? $this->telefonoa : (string) (auth()->user()->telefonoa ?? '');

        $rows = Erreserba::query()
            ->select(['mahaiak_id', 'telefonoa'])
            ->where('eguna_ordua', '>', $from)
            ->where('eguna_ordua', '<', $to)
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
            ->orderByDesc('eguna_ordua')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    public function seleccionarMahaia(int $mahaiId): void
    {
        if (! $this->featureEnabled) {
            session()->flash('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return;
        }

        if (! auth()->check()) {
            $this->redirect(route('login', absolute: false));
            return;
        }

        $user = auth()->user();
        if (! $user || (string) ($user->telefonoa ?? '') === '') {
            session()->flash('error', 'Ezin da erreserbatu: ez duzu telefonoa ezarrita.');
            return;
        }

        $this->syncOrdua();
        $egunaOrdua = $this->getEgunaOrdua();
        if (! $egunaOrdua) {
            session()->flash('error', 'Aukeratu ordua.');
            return;
        }

        if ($this->pertsonaKopurua < 1) {
            session()->flash('error', 'Pertsona kopurua ezin da 1 baino txikiagoa izan.');
            return;
        }

        $mahai = Mahai::query()->find($mahaiId);
        if (! $mahai) {
            session()->flash('error', 'Ez da mahaia aurkitu.');
            return;
        }

        if (mb_strtolower((string) ($mahai->kokapena ?? '')) === 'terraza' && ! $this->terrazaDisponible) {
            session()->flash('error', 'Terraza itxita dago eguraldiagatik.');
            return;
        }

        $cap = (int) ($mahai->pertsona_kopurua ?? 0);
        if ($cap > 0 && $cap < $this->pertsonaKopurua) {
            session()->flash('error', 'Mahaia txikiegia da aukeratutako pertsona kopururako.');
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

    public function confirmarReserva(): void
    {
        if (! $this->selectedMahaiId) return;

        $id = $this->selectedMahaiId;
        $this->cerrarConfirm();
        $this->reservar($id);
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
            session()->flash('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return redirect()->route('reservas');
        }

        $user = Auth::user();
        if (! $user || ! $this->selectedReservaId) {
            $this->cerrarCancel();
            return;
        }

        $telefonoa = $this->telefonoa !== '' ? $this->telefonoa : (string) ($user->telefonoa ?? '');
        if ($telefonoa === '') {
            session()->flash('error', 'Ezin da ezeztatu: ez duzu telefonoa ezarrita.');
            return redirect()->route('reservas');
        }

        $reservaId = $this->selectedReservaId;
        $this->cerrarCancel();

        try {
            $r = Erreserba::query()
                ->where('id', $reservaId)
                ->where('telefonoa', $telefonoa)
                ->first();

            if (! $r) {
                session()->flash('error', 'Ez da erreserba hori aurkitu.');
                return redirect()->route('reservas');
            }

            // Solo permitimos cancelar hoy o futuro
            if ($r->eguna_ordua->lt(now()->startOfDay())) {
                session()->flash('error', 'Gaurko edo etorkizuneko erreserbak bakarrik ezeztatu ditzakezu.');
                return redirect()->route('reservas');
            }

            DB::transaction(function () use ($r) {
                foreach (['eskariak', 'eskaariak'] as $ordersTable) {
                    if (! Schema::hasTable($ordersTable) || ! Schema::hasColumn($ordersTable, 'erreserbak_id') || ! Schema::hasColumn($ordersTable, 'id')) {
                        continue;
                    }

                    $orderIds = DB::table($ordersTable)
                        ->where('erreserbak_id', $r->id)
                        ->pluck('id')
                        ->filter(fn ($id) => $id !== null)
                        ->values()
                        ->all();

                    if (! $orderIds) {
                        continue;
                    }

                    foreach (['eskariak_has_produktuak', 'eskaariak_has_produktuak'] as $pivotTable) {
                        if (! Schema::hasTable($pivotTable)) {
                            continue;
                        }

                        foreach (['eskariak_id', 'eskaariak_id'] as $pivotFk) {
                            if (! Schema::hasColumn($pivotTable, $pivotFk)) {
                                continue;
                            }

                            DB::table($pivotTable)->whereIn($pivotFk, $orderIds)->delete();
                        }
                    }

                    DB::table($ordersTable)->whereIn('id', $orderIds)->delete();
                }

                if (DB::getDriverName() === 'mysql') {
                    $dbName = DB::connection()->getDatabaseName();

                    $refs = DB::table('information_schema.KEY_COLUMN_USAGE')
                        ->select(['TABLE_NAME', 'CONSTRAINT_NAME', 'COLUMN_NAME', 'REFERENCED_COLUMN_NAME'])
                        ->where('REFERENCED_TABLE_SCHEMA', $dbName)
                        ->where('REFERENCED_TABLE_NAME', 'erreserbak')
                        ->whereNotNull('REFERENCED_COLUMN_NAME')
                        ->get();

                    $groups = [];
                    foreach ($refs as $ref) {
                        $table = (string) ($ref->TABLE_NAME ?? '');
                        $constraint = (string) ($ref->CONSTRAINT_NAME ?? '');
                        $column = (string) ($ref->COLUMN_NAME ?? '');
                        $refColumn = (string) ($ref->REFERENCED_COLUMN_NAME ?? '');

                        if ($table === '' || $constraint === '' || $column === '' || $refColumn === '' || $table === 'erreserbak') {
                            continue;
                        }

                        $key = $table.'|'.$constraint;
                        $groups[$key]['table'] = $table;
                        $groups[$key]['pairs'][] = [$column, $refColumn];
                    }

                    foreach ($groups as $group) {
                        $table = (string) ($group['table'] ?? '');
                        $pairs = (array) ($group['pairs'] ?? []);

                        if ($table === '' || ! Schema::hasTable($table) || ! $pairs) {
                            continue;
                        }

                        $q = DB::table($table);
                        $hasWhere = false;
                        foreach ($pairs as $pair) {
                            [$column, $refColumn] = $pair;
                            if (! is_string($column) || ! is_string($refColumn)) {
                                continue;
                            }
                            if (! Schema::hasColumn($table, $column)) {
                                continue;
                            }

                            $val = $r->getAttribute($refColumn);
                            if ($val === null) {
                                continue;
                            }

                            $q->where($column, $val);
                            $hasWhere = true;
                        }

                        if ($hasWhere) {
                            $q->delete();
                        }
                    }
                } elseif (Schema::hasTable('eskariak') && Schema::hasColumn('eskariak', 'erreserbak_id')) {
                    DB::table('eskariak')->where('erreserbak_id', $r->id)->delete();
                }

                DB::table('erreserbak')
                    ->where('id', $r->id)
                    ->delete();
            }, 3);

            session()->flash('success', 'Erreserba ezeztatuta.');
            return redirect()->route('reservas');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Errore bat gertatu da ezeztatzean.');
            return redirect()->route('reservas');
        }
    }

    public function reservar(int $mahaiId)
    {
        if (! $this->featureEnabled) {
            session()->flash('error', 'Erreserbak ez daude erabilgarri une honetan.');
            return redirect()->route('reservas');
        }

        $user = Auth::user();
        if (! $user) {
            $this->redirect(route('login', absolute: false));
            return;
        }

        $this->syncOrdua();
        $egunaOrdua = $this->getEgunaOrdua();
        if (! $egunaOrdua) {
            session()->flash('error', 'Aukeratu ordua.');
            return redirect()->route('reservas');
        }

        if ($egunaOrdua->lt(now())) {
            session()->flash('error', 'Ezin da iraganeko ordu batean erreserbatu.');
            return redirect()->route('reservas');
        }

        if ($egunaOrdua->lt(now()->addHour())) {
            session()->flash('error', 'Erreserbatzeko, gutxienez 1 orduko aurrerapena behar da.');
            return redirect()->route('reservas');
        }

        $bezeroIzena = trim((string) ($user->name ?? $this->bezeroIzena));
        $telefonoa = trim((string) ($user->telefonoa ?? $this->telefonoa));
        if ($bezeroIzena === '' || $telefonoa === '') {
            session()->flash('error', 'Ezin da erreserbatu: falta dira zure datuak (izena edo telefonoa).');
            return redirect()->route('reservas');
        }

        if ($this->pertsonaKopurua < 1) {
            session()->flash('error', 'Pertsona kopurua ezin da 1 baino txikiagoa izan.');
            return redirect()->route('reservas');
        }

        $lockKey = 'erreserbak:'.$mahaiId.':'.$egunaOrdua->format('Ymd');
        $lockAcquired = false;
        $committed = false;

        try {
            if (DB::getDriverName() === 'mysql') {
                $row = DB::selectOne('SELECT GET_LOCK(?, ?) AS l', [$lockKey, 10]);
                $lockAcquired = (int) ($row->l ?? 0) === 1;

                if (! $lockAcquired) {
                    session()->flash('error', 'Barkatu, erreserba hau beste erabiltzaile batek egiten ari da une honetan. Saiatu berriro.');
                    return redirect()->route('reservas');
                }
            }

            // Set isolation level to SERIALIZABLE to avoid race conditions
            DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
            DB::beginTransaction();

            $from = $egunaOrdua->copy()->subHours(2);
            $to = $egunaOrdua->copy()->addHours(2);

            $yaOcupada = Erreserba::query()
                ->where('mahaiak_id', $mahaiId)
                ->where('eguna_ordua', '>', $from)
                ->where('eguna_ordua', '<', $to)
                ->exists();

            if ($yaOcupada) {
                DB::rollBack();
                session()->flash('error', 'Barkatu, mahaia okupatuta dago aukeratutako ordurako.');
                return redirect()->route('reservas');
            }

            $mahai = Mahai::query()->find($mahaiId);
            if (! $mahai) {
                DB::rollBack();
                session()->flash('error', 'Ez da mahaia aurkitu.');
                return redirect()->route('reservas');
            }

            if (mb_strtolower((string) ($mahai->kokapena ?? '')) === 'terraza' && ! $this->terrazaDisponible) {
                DB::rollBack();
                session()->flash('error', 'Terraza itxita dago eguraldiagatik.');
                return redirect()->route('reservas');
            }

            $cap = (int) ($mahai->pertsona_kopurua ?? 0);
            if ($cap > 0 && $cap < $this->pertsonaKopurua) {
                DB::rollBack();
                session()->flash('error', 'Mahaia txikiegia da aukeratutako pertsona kopururako.');
                return redirect()->route('reservas');
            }

            $langileId = (int) (DB::table('langileak')->where('ezabatua', 0)->min('id') ?? DB::table('langileak')->min('id') ?? 1);

            $erreserba = Erreserba::create([
                'bezero_izena' => $bezeroIzena,
                'telefonoa' => $telefonoa,
                'pertsona_kopurua' => (int) $this->pertsonaKopurua,
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
                $reservasUrl = rtrim((string) config('app.url'), '/').route('reservas', absolute: false);

                Mail::to($user->email)->send(new ReservationConfirmedEmail($erreserba, $reservasUrl));
                $emailSent = true;
            } catch (\Throwable $mailError) {
                report($mailError);
            }

            session()->flash('success', $emailSent ? 'Erreserba baieztatuta! Mezu bat bidali da.' : 'Erreserba baieztatuta!');
            return redirect()->route('reservas');
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            report($e);
            session()->flash('error', 'Errore bat gertatu da erreserbatzean.');
            return redirect()->route('reservas');
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
                Aukeratu eguna, ordua eta pertsonak; gero egin klik mahai libre batean.
            </p>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-zinc-200 p-6">
                <h2 class="font-semibold">Aukeratu data eta ordua</h2>

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
                        <p class="mt-1 text-xs text-zinc-500">Gutxienez 1 orduko aurrerapenarekin.</p>
                        @if (count($this->orduaAukerak) === 0)
                            <div class="mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                Ez dago ordurik erabilgarri aukeratutako egunerako.
                            </div>
                        @else
                            <select
                                wire:model.live="ordua"
                                class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#6366F1]"
                            >
                                @foreach ($this->orduaAukerak as $o)
                                    <option value="{{ $o }}">{{ $o }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Pertsona kopurua</label>
                        <input
                            type="number"
                            min="1"
                            wire:model.live="pertsonaKopurua"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#6366F1]"
                        />
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200">
                        <div class="text-sm font-medium">Legenda</div>
                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded bg-[#4E8EF7]"></span>
                                Libre
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded bg-red-500"></span>
                                Zure erreserba
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-3.5 w-3.5 rounded" style="background-color: #374151;"></span>
                                Okupatuta
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 rounded-2xl border border-zinc-200 p-6">
                <h2 class="font-semibold">Mahai erabilgarriak</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    Data: <strong>{{ \Carbon\Carbon::parse($data ?: now()->toDateString())->format('d/m/Y') }}</strong> ·
                    Ordua: <strong>{{ $ordua ?: '—' }}</strong> ·
                    Pertsonak: <strong>{{ (int) $pertsonaKopurua }}</strong>
                </p>

                @php
                    $requiredPeople = (int) $pertsonaKopurua;
                    $myPhone = (string) (auth()->user()?->telefonoa ?? '');
                    $terrazaOn = $this->terrazaDisponible;

                    $renderBtn = function($mahai) use ($requiredPeople, $myPhone, $terrazaOn) {
                        if (! $mahai) return;

                        $kokapena = mb_strtolower((string) ($mahai->kokapena ?? ''));
                        $ocupadaTelefonoa = $this->ocupadas[$mahai->id] ?? null;
                        $ocupada = $ocupadaTelefonoa !== null;
                        $esMia = $ocupada && $ocupadaTelefonoa === ($myPhone !== '' ? $myPhone : (auth()->user()?->telefonoa ?? null));

                        $cap = (int) ($mahai->pertsona_kopurua ?? 0);
                        $txikiegia = $cap > 0 && $cap < $requiredPeople;
                        $terrazaItxita = $kokapena === 'terraza' && ! $terrazaOn;

                        $bgColor = '#4E8EF7';
                        if ($ocupada) {
                            $bgColor = $esMia ? '#EF4444' : '#4b5563';
                        } elseif ($txikiegia || $terrazaItxita) {
                            $bgColor = '#9ca3af';
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
                        </button>';
                    };

                    $interior = $this->mahaiak->filter(fn ($m) => mb_strtolower((string) ($m->kokapena ?? '')) === 'interior');
                    $terraza = $this->mahaiak->filter(fn ($m) => mb_strtolower((string) ($m->kokapena ?? '')) === 'terraza');
                    $ekitaldi = $this->mahaiak->filter(fn ($m) => mb_strtolower((string) ($m->kokapena ?? '')) === 'ekitaldi');
                @endphp

                <div class="mt-6 space-y-8">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Interior</div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($interior as $m)
                                {!! $renderBtn($m) !!}
                            @endforeach
                        </div>
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
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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

                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Ekitaldi</div>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($ekitaldi as $m)
                                {!! $renderBtn($m) !!}
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-xl bg-zinc-50 p-4 border border-zinc-200">
                    <div class="text-sm font-medium">Zure erreserbak</div>
                    <div class="mt-3 space-y-2 text-sm">
                        @forelse ($this->misReservas as $r)
                            @php
                                $esFutura = $r->eguna_ordua->gte(now()->startOfDay());
                                $puedeCancelar = $esFutura;
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2">
                                <div>
                                    <div>
                                        <span class="font-semibold">{{ $r->eguna_ordua->format('d/m/Y H:i') }}</span>
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
                            <div class="text-zinc-500">Oraindik ez duzu erreserbarik.</div>
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
                <h3 class="text-lg font-bold">Erreserba baieztatu</h3>
                <p class="mt-2 text-sm text-zinc-600">
                    Erreserbatuko duzu: <strong>Mahaia {{ $this->selectedMahai?->zenbakia ?? ($selectedMahaiId ?? '') }}</strong>
                    egunerako: <strong>{{ \Carbon\Carbon::parse($data ?: now()->toDateString())->format('d/m/Y') }}</strong> ·
                    ordua: <strong>{{ $ordua ?: '—' }}</strong> ·
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
                    Ziur zaude erreserba hau ezeztatu nahi duzula? Ekintza hau ezin da desegin.
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
