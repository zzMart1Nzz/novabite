@php
    $groups = [
        'Plataforma' => [
            [
                'name' => 'Arbela',
                'icon' => 'home',
                'url' => route('dashboard', absolute: false),
                'current' => request()->routeIs('dashboard'),
            ],
        ],
    ]
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#F9FAFB] text-slate-800">
        <!-- Header -->
        @php
            $nav = [
                ['label' => 'Hasiera', 'route' => 'home'],
                ['label' => 'Gure Filosofia', 'route' => 'filosofia'],
                ['label' => 'Menua', 'route' => 'menu'],
                ['label' => 'Jatetxeak', 'route' => 'restaurantes'],
            ];
        @endphp

        <flux:header container class="border-b border-[#E5E7EB] bg-[#6366F1] {{ (request()->routeIs('profile.edit') || request()->routeIs('user-password.edit') || request()->routeIs('two-factor.show')) ? 'hidden xl:block' : '' }}">
            <div class="flex w-full items-center gap-3 py-4">
                <flux:sidebar.toggle class="lg:hidden text-white" icon="bars-2" inset="left" />

                <a href="{{ route('home', absolute: false) }}" class="flex items-baseline gap-2 text-white" wire:navigate>
                    <span class="text-xl sm:text-2xl font-black tracking-[0.18em]" style="font-family: Arial, Helvetica, sans-serif;">NOVA BITES</span>
                    <span class="hidden sm:inline text-xs opacity-90">Jatetxea &amp; erreserbak</span>
                </a>

                <div class="ms-auto flex items-center gap-3">
                    <flux:dropdown position="top" align="end">
                        <flux:profile class="cursor-pointer text-white" :initials="auth()->user()->initials()" />

                        <flux:menu class="bg-white border border-zinc-200">
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal text-zinc-900">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black">
                                                    {{ auth()->user()->initials() }}
                                                </span>
                                            </span>

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs text-zinc-500">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>
                            
                            <flux:menu.separator class="bg-zinc-200" />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('profile.edit', absolute: false)" icon="cog" class="!text-zinc-900" wire:navigate>Ezarpenak</flux:menu.item>
                                <flux:menu.item :href="route('reservas.historial', absolute: false)" icon="calendar-days" class="!text-zinc-900" wire:navigate>Nire erreserbak</flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator class="bg-zinc-200" />

                            <form method="POST" action="{{ route('logout', absolute: false) }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-sm font-medium text-zinc-900 hover:bg-zinc-100 transition-colors" data-test="logout-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-zinc-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Saioa itxi
                                </button>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </flux:header>

        @if (!request()->routeIs('profile.edit') && !request()->routeIs('user-password.edit') && !request()->routeIs('two-factor.show'))
        <flux:sidebar sticky stashable class="border-e border-zinc-200 text-white" style="background-color: #4338CA;">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:navlist variant="outline">
                @foreach ($groups as $group => $links)
                    
                
                    <flux:navlist.group :heading="$group" class="grid">

                        @foreach ($links as $link)
                            <flux:navlist.item :icon="$link['icon']" :href="$link['url']" :current="$link['current']" class="text-white hover:text-white/80" wire:navigate>{{ $link['name'] }}</flux:navlist.item>
                        @endforeach

                    </flux:navlist.group>

                @endforeach
            </flux:navlist>

            <flux:spacer />
        </flux:sidebar>
        @endif

        {{ $slot }}

        @fluxScripts
        
        <!-- Toastr JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            @if(Session::has('success'))
                toastr.success("{{ Session::get('success') }}");
            @endif

            @if(Session::has('error'))
                toastr.error("{{ Session::get('error') }}");
            @endif

            @if(Session::has('info'))
                toastr.info("{{ Session::get('info') }}");
            @endif

            @if(Session::has('warning'))
                toastr.warning("{{ Session::get('warning') }}");
            @endif
        </script>
    </body>
</html>
