@php
    $nav = [
        ['label' => 'Hasiera', 'route' => 'home', 'icon' => 'home'],
        ['label' => 'Gure Filosofia', 'route' => 'filosofia', 'icon' => 'book-open'],
        ['label' => 'Menua', 'route' => 'menu', 'icon' => 'clipboard-document-list'],
        ['label' => 'Jatetxeak', 'route' => 'restaurantes', 'icon' => 'building-storefront'],
    ];
@endphp

<div x-data="{ settingsMobileMenuOpen: false }" class="flex items-start min-h-screen">
    <!-- Mobile Floating Button -->
    <button 
        type="button" 
        class="fixed top-4 left-4 z-50 p-3 bg-[#6366F1] text-white rounded-full shadow-lg hover:bg-[#4F46E5] transition-colors"
        @click="settingsMobileMenuOpen = !settingsMobileMenuOpen"
        x-show="!settingsMobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Mobile Menu Overlay -->
    <div 
        x-show="settingsMobileMenuOpen" 
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="settingsMobileMenuOpen = false"
        style="display: none;"
    ></div>

    <!-- Mobile Menu Sidebar -->
    <div 
        x-show="settingsMobileMenuOpen" 
        class="fixed inset-y-0 left-0 z-50 w-[85%] sm:w-[20%] min-w-[250px] bg-[#6366F1] shadow-2xl transform transition-transform duration-300 ease-in-out overflow-y-auto"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        style="display: none;"
    >
        <div class="flex items-center justify-between p-4 border-b border-white/10">
            <span class="text-base font-black tracking-[0.18em] text-white">NOVA BITES</span>
            <button type="button" class="text-white hover:bg-white/10 rounded-full p-1 transition" @click="settingsMobileMenuOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-4 space-y-6">
            <!-- Global Navigation -->
            <div>
                <div class="text-xs font-semibold text-white/60 uppercase tracking-wider mb-2">Nabigazioa</div>
                <div class="space-y-1">
                    @foreach ($nav as $item)
                        <a 
                            href="{{ route($item['route']) }}" 
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition"
                            wire:navigate
                            @click="settingsMobileMenuOpen = false"
                        >
                            @if($item['icon'] === 'home')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            @elseif($item['icon'] === 'book-open')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                            @elseif($item['icon'] === 'clipboard-document-list')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.091-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                            @elseif($item['icon'] === 'building-storefront')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Settings Navigation -->
            <div>
                <div class="text-xs font-semibold text-white/60 uppercase tracking-wider mb-2">Ezarpenak</div>
                <div class="space-y-1">
                    <a href="#profile" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition" @click="settingsMobileMenuOpen = false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Profila
                    </a>
                    <a href="#password" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition" @click="settingsMobileMenuOpen = false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Pasahitza
                    </a>
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <a href="#two-factor" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition" @click="settingsMobileMenuOpen = false">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            Bi urratseko autentifikazioa
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="mt-auto p-4 border-t border-white/10 absolute bottom-0 w-full">
            <div class="flex items-center gap-3 px-2">
                 <div class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full bg-white/10 items-center justify-center text-white font-bold">
                     {{ auth()->user()->initials() }}
                 </div>
                 <div class="grid flex-1 text-start text-sm leading-tight text-white">
                     <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                     <span class="truncate text-xs opacity-70">{{ auth()->user()->email }}</span>
                 </div>
            </div>
            
            <div class="h-px bg-white/10 my-3"></div>
            
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left text-sm font-medium text-white hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    Saioa itxi
                </button>
            </form>
        </div>
    </div>

    <!-- Desktop Sidebar (Removed per user request) -->
    {{-- <div class="hidden lg:flex w-full md:w-[250px] min-h-[calc(100vh-80px)] p-6 flex-col gap-6 -mt-px -ml-px" style="background-color: #8E3339;">
        <div class="text-white">
            <flux:heading size="xl" level="1" class="text-white! mb-2">{{ __('Ezarpenak') }}</flux:heading>
            <flux:subheading size="lg" class="text-white/80!">{{ __('Kudeatu zure profila eta kontuaren ezarpenak') }}</flux:subheading>
        </div>
        
        <flux:navlist>
            <flux:navlist.item :href="route('profile.edit')" wire:navigate class="text-white hover:text-white/80" style="--color-accent-content: #ffffff; --color-zinc-800: rgba(255,255,255,0.1);">{{ __('Profila') }}</flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate class="text-white hover:text-white/80" style="--color-accent-content: #ffffff; --color-zinc-800: rgba(255,255,255,0.1);">{{ __('Pasahitza') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate class="text-white hover:text-white/80" style="--color-accent-content: #ffffff; --color-zinc-800: rgba(255,255,255,0.1);">{{ __('Bi urratseko autentifikazioa') }}</flux:navlist.item>
            @endif
        </flux:navlist>
    </div> --}}

    <!-- Content -->
    <div class="flex-1 self-stretch p-8 pt-16 pl-24 md:pl-32 max-w-6xl mx-auto">
        @if(isset($heading) || isset($subheading))
            <div class="mb-8">
                <flux:heading size="xl" class="mb-2 text-3xl font-bold text-gray-900">{{ $heading ?? '' }}</flux:heading>
                <flux:subheading class="mb-6 text-lg text-gray-600">{{ $subheading ?? '' }}</flux:subheading>
            </div>
        @endif

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
