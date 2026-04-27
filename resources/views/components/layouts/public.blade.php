<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-zinc-900">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-[#F9FAFB] text-slate-800 flex flex-col overscroll-none" x-data="{ mobileMenuOpen: false }" :class="{ 'overflow-hidden': mobileMenuOpen }">
        
        <!-- Fixed Floating Menu Button -->
        <button 
            type="button" 
            class="fixed top-4 left-4 z-50 p-3 bg-[#6366F1] text-white rounded-full shadow-lg hover:bg-[#4F46E5] transition-colors md:hidden"
            @click="mobileMenuOpen = !mobileMenuOpen"
            x-show="!mobileMenuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        @php
            $nav = [
                ['label' => 'Hasiera', 'route' => 'home', 'icon' => 'home'],
                ['label' => 'Gure Filosofia', 'route' => 'filosofia', 'icon' => 'book-open'],
                ['label' => 'Menua', 'route' => 'menu', 'icon' => 'clipboard-document-list'],
                ['label' => 'Jatetxeak', 'route' => 'restaurantes', 'icon' => 'building-storefront'],
            ];
        @endphp

        <!-- Desktop Header -->
        <header class="hidden sm:block w-full border-b border-[#E5E7EB] bg-[#6366F1]">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex w-full items-center gap-3 py-4">
                    <a href="{{ route('home') }}" class="flex items-baseline gap-2 text-white" wire:navigate>
                        <span class="text-xl sm:text-2xl font-black tracking-[0.18em]" style="font-family: Arial, Helvetica, sans-serif;">NOVA BITES</span>
                        <span class="hidden sm:inline text-xs opacity-90">Jatetxea &amp; erreserbak</span>
                    </a>

                    <div class="flex flex-1 justify-center">
                        <div class="flex items-center gap-6">
                            @foreach ($nav as $item)
                                <a 
                                    href="{{ route($item['route']) }}" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-white hover:text-white/80 transition rounded-lg {{ request()->routeIs($item['route']) ? 'bg-white/10' : '' }}"
                                    wire:navigate
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

                            @auth
                                <a 
                                    href="{{ route('reservas') }}" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm font-bold text-white hover:text-white/80 transition rounded-lg {{ request()->routeIs('reservas') ? 'bg-white/10' : '' }}"
                                    wire:navigate
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                    </svg>
                                    Mahaia Erreserbatu
                                </a>
                            @endauth
                        </div>
                    </div>

                <div class="ms-auto flex items-center gap-3">
                    @auth
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
                                    <flux:menu.item :href="route('profile.edit')" icon="cog" class="!text-zinc-900" wire:navigate>Ezarpenak</flux:menu.item>
                                </flux:menu.radio.group>

                                <flux:menu.separator class="bg-zinc-200" />

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
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
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium text-white hover:text-gray-100 transition" wire:navigate>
                            Saioa hasi
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-medium text-[#6366F1] hover:bg-zinc-100 transition shadow-sm" wire:navigate>
                            Erregistratu
                        </a>
                    @endauth
                </div>
            </div>
            </div>
        </header>

        <!-- Mobile Menu Overlay -->
        <div 
            x-show="mobileMenuOpen" 
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm sm:hidden"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
            style="display: none;"
        ></div>

        <!-- Mobile Menu Sidebar -->
        <div 
            x-show="mobileMenuOpen" 
            class="fixed inset-y-0 left-0 z-50 w-[85%] sm:w-[20%] min-w-[250px] bg-[#6366F1] shadow-2xl transform transition-transform duration-300 ease-in-out md:hidden"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            style="display: none;"
        >
            <div class="flex items-center justify-between p-4 border-b border-white/10">
                <a href="{{ route('home') }}" class="text-base font-black tracking-[0.18em] text-white" wire:navigate @click="mobileMenuOpen = false">
                    NOVA BITES
                </a>
                <button type="button" class="text-white hover:bg-white/10 rounded-full p-1 transition" @click="mobileMenuOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-1">
                <div class="text-xs font-semibold text-white/60 uppercase tracking-wider mb-2">Nabigazioa</div>
                
                @foreach ($nav as $item)
                    <a 
                        href="{{ route($item['route']) }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition {{ request()->routeIs($item['route']) ? 'bg-white/10' : '' }}"
                        wire:navigate
                        @click="mobileMenuOpen = false"
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

                @auth
                    <a 
                        href="{{ route('reservas') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition {{ request()->routeIs('reservas') ? 'bg-white/10' : '' }}"
                        wire:navigate
                        @click="mobileMenuOpen = false"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        Mahaia Erreserbatu
                    </a>
                @endauth
            </div>

            <div class="mt-auto p-4 border-t border-white/10 absolute bottom-0 w-full">
                @auth
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-3 px-2">
                             <div class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full bg-white/10 items-center justify-center text-white font-bold">
                                 {{ auth()->user()->initials() }}
                             </div>
                             <div class="grid flex-1 text-start text-sm leading-tight text-white">
                                 <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                 <span class="truncate text-xs opacity-70">{{ auth()->user()->email }}</span>
                             </div>
                        </div>
                
                        <div class="h-px bg-white/10 my-1"></div>
                
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition" wire:navigate>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Ezarpenak
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ml-auto size-4 opacity-50">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-white hover:bg-white/10 transition text-start">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                </svg>
                                Saioa itxi
                            </button>
                        </form>
                    </div>
                @else
                    <div class="grid gap-3">
                        <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-medium text-[#6366F1] transition shadow-sm" wire:navigate>
                            Saioa hasi
                        </a>
                        <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition" wire:navigate>
                            Erregistratu
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-zinc-900 text-white py-12 mt-auto">
            <div class="mx-auto max-w-6xl px-4 text-center">
                <div class="mb-6">
                    <span class="text-2xl font-black tracking-[0.18em]" style="font-family: Arial, Helvetica, sans-serif;">NOVA BITES</span>
                </div>
                <div class="space-y-2 text-zinc-400">
                    <p>Argi Kalea 12, 20240 Ordizia, Gipuzkoa</p>
                    <p>+34 943 00 00 00</p>
                    <p>info@novabites.com</p>
                    <p class="mt-4 pt-4 border-t border-zinc-800 text-sm">© {{ date('Y') }} Nova Bites. Eskubide guztiak erreserbatuta.</p>
                </div>
            </div>
        </footer>

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

            @if(Session::has('warning'))
                toastr.warning("{{ Session::get('warning') }}");
            @endif

            @if(Session::has('info'))
                toastr.info("{{ Session::get('info') }}");
            @endif
        </script>

        @fluxScripts
    </body>
</html>
