<x-layouts.app :title="__('Dashboard')">
    <div class="rounded-2xl border border-zinc-200 p-6">
        <h1 class="text-2xl font-bold">Kaixo, {{ auth()->user()->name }} 👋</h1>
        <p class="mt-2 text-zinc-600">
            Hemendik zure kontua kudeatu eta erreserbak egin ditzakezu.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('reservas') }}" class="inline-flex items-center justify-center rounded-xl bg-[#6366F1] px-5 py-3 font-semibold text-white hover:bg-[#4F46E5]" wire:navigate>
                Mahaia erreserbatu
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 px-5 py-3 font-semibold hover:bg-zinc-50" wire:navigate>
                Webgunera itzuli
            </a>
        </div>
    </div>
</x-layouts.app>
