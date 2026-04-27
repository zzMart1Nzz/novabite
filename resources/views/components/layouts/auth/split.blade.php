<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="relative flex min-h-screen flex-col lg:grid lg:max-w-none lg:grid-cols-2 lg:px-0">
            <!-- Parte izquierda: sólo imagen (visible en lg) -->
            <div
                class="relative hidden h-full flex-col p-10 text-black lg:flex"
                style="background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 45%, #DBEAFE 100%);">
                <!-- overlay para mejorar contraste -->
                {{-- <div class="absolute inset-0" style="background-color: rgba(0,0,0,0.35);"></div> --}}

                <!-- Si quieres mantener un poco de espacio/estructura, puedes dejar este contenedor vacío -->
                <div class="relative z-10 flex flex-1 flex-col justify-between">
                    

                    <div class="relative z-10 mt-16">
                        <h1 class="text-4xl font-bold tracking-tight">Ongi etorri Nova Bitesera</h1>
                        <p class="mt-4 text-lg">Janari ona eta giro atsegina</p>
                    </div>

                    <div class="relative z-10 text-sm opacity-50">
                        &copy; {{ date('Y') }} Nova Bites. Eskubide guztiak erreserbatuta.
                    </div>
                </div>
            </div>

            <!-- Parte derecha: fondo con color #B5424B y formulario centrado (ocupa toda la columna) -->
            <div class="w-full flex-1 p-8 md:p-8 text-white flex items-center justify-center" style="background: linear-gradient(135deg, #4F8DF7 0%, #4338CA 100%);">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-1 font-medium md:hidden" wire:navigate>
                        <span class="text-3xl font-black tracking-[0.18em] text-white" style="font-family: Arial, Helvetica, sans-serif;">NOVA BITES</span>
                        <span class="text-xs opacity-90 text-white">Jatetxea &amp; erreserbak</span>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
