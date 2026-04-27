<x-layouts.public>
    <section class="mx-auto max-w-6xl px-4 py-16 sm:py-24">
        <div class="text-center mb-16">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">Gure menua</h1>
            <p class="mt-4 text-xl text-zinc-600 max-w-2xl mx-auto">
                Aukera errazak eta ondo eginak, denontzat.
            </p>
        </div>

        @if (!$tablasDisponibles)
            <div class="text-center text-zinc-500 py-10">
                Menua ez dago erabilgarri une honetan.
            </div>
        @else
            <div class="space-y-16">
                @if (!empty($usaMotaTexto))
                    @if ($categorias->isEmpty())
                        <div class="text-center text-zinc-500 py-10">
                            Une honetan ez dago produkturik erabilgarri.
                        </div>
                    @endif
                    @foreach ($categorias as $categoria)
                        @php
                            $items = $productosPorCategoria[$categoria['key']] ?? collect();
                        @endphp

                        @if ($items->isNotEmpty())
                            <div>
                                <h2 class="text-3xl font-bold text-slate-900 mb-8 border-b border-[#E5E7EB] pb-4">{{ $categoria['label'] }}</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    @foreach ($items as $plato)
                                        @php
                                            $image = $plato->irudia ?? $plato->irudia_path ?? null;
                                            $publicImageExists = $image && file_exists(public_path('images/' . $image));
                                            $externalImageExists = $image && file_exists(base_path('external_api/images/' . $image));
                                            $imageExists = $publicImageExists || $externalImageExists;
                                            $imageUrl = $publicImageExists
                                                ? asset('images/' . $image)
                                                : ($externalImageExists ? route('external.images.show', ['filename' => $image]) : null);
                                            $remoteImageUrl = $plato->irudia_url ?? null;
                                        @endphp

                                        <div class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-sm hover:shadow-md transition">
                                            <div class="flex flex-col gap-4">
                                                @if($remoteImageUrl)
                                                    <div class="w-full h-56 rounded-xl bg-zinc-100 flex items-center justify-center p-3">
                                                        <img src="{{ $remoteImageUrl }}" alt="{{ $plato->izena }}" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                @elseif($imageExists)
                                                    <div class="w-full h-56 rounded-xl bg-zinc-100 flex items-center justify-center p-3">
                                                        <img src="{{ $imageUrl }}" alt="{{ $plato->izena }}" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                @endif
                                                
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h3 class="font-bold text-lg text-zinc-900">{{ $plato->izena }}</h3>
                                                    </div>
                                                    <span class="font-bold text-[#6366F1] text-lg">{{ number_format($plato->prezioa, 2) }}€</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    @foreach ($tipos as $tipo)
                        @if (isset($platosPorTipo[$tipo->id]) && $platosPorTipo[$tipo->id]->isNotEmpty())
                            <div>
                                <h2 class="text-3xl font-bold text-slate-900 mb-8 border-b border-[#E5E7EB] pb-4">{{ $tipo->izena }}</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    @foreach ($platosPorTipo[$tipo->id] as $plato)
                                        @php
                                            $image = $plato->irudia ?? $plato->irudia_path ?? null;
                                            $publicImageExists = $image && file_exists(public_path('images/' . $image));
                                            $externalImageExists = $image && file_exists(base_path('external_api/images/' . $image));
                                            $imageExists = $publicImageExists || $externalImageExists;
                                            $imageUrl = $publicImageExists
                                                ? asset('images/' . $image)
                                                : ($externalImageExists ? route('external.images.show', ['filename' => $image]) : null);
                                            $remoteImageUrl = $plato->irudia_url ?? null;
                                        @endphp

                                        <div class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-sm hover:shadow-md transition">
                                            <div class="flex flex-col gap-4">
                                                @if($remoteImageUrl)
                                                    <div class="w-full h-56 rounded-xl bg-zinc-100 flex items-center justify-center p-3">
                                                        <img src="{{ $remoteImageUrl }}" alt="{{ $plato->izena }}" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                @elseif($imageExists)
                                                    <div class="w-full h-56 rounded-xl bg-zinc-100 flex items-center justify-center p-3">
                                                        <img src="{{ $imageUrl }}" alt="{{ $plato->izena }}" class="max-w-full max-h-full object-contain">
                                                    </div>
                                                @endif
                                                
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h3 class="font-bold text-lg text-zinc-900">{{ $plato->izena }}</h3>
                                                    </div>
                                                    <span class="font-bold text-[#6366F1] text-lg">{{ number_format($plato->prezioa, 2) }}€</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        @endif
    </section>
</x-layouts.public>
