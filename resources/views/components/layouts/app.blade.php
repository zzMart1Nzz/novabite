<x-layouts.app.sidebar :title="$title ?? null">
    @if (request()->routeIs('profile.edit') || request()->routeIs('user-password.edit') || request()->routeIs('two-factor.show'))
        <div class="w-full">
            {{ $slot }}
        </div>
    @else
        <flux:main>
            {{ $slot }}
        </flux:main>
    @endif
</x-layouts.app.sidebar>
