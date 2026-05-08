<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            .nb-auth-media {
                display: none;
            }

            .nb-auth-mobile-brand {
                display: flex;
            }

            @media (min-width: 1280px) {
                .nb-desktop-auth-grid {
                    display: grid;
                    max-width: none;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    padding-left: 0;
                    padding-right: 0;
                }

                .nb-auth-media {
                    display: flex;
                }

                .nb-auth-mobile-brand {
                    display: none;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="relative flex min-h-screen flex-col nb-desktop-auth-grid">
            <!-- Parte izquierda: sólo imagen (visible en lg) -->
            <div
                class="nb-auth-media relative h-full flex-col p-10 text-black"
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
                    <a href="{{ route('home', absolute: false) }}" class="nb-auth-mobile-brand z-20 flex-col items-center gap-1 font-medium" wire:navigate>
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
