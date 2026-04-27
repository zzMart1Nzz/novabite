<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class PublicPagesController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function filosofia()
    {
        return view('pages.filosofia');
    }

    public function restaurantes()
    {
        return view('pages.restaurantes');
    }

    public function menu()
    {
        $apiBaseUrl = env('NOVABITES_PRODUCTS_API_URL');

        if (! is_string($apiBaseUrl) || trim($apiBaseUrl) === '') {
            return view('pages.menu', [
                'categorias' => collect(),
                'productosPorCategoria' => collect(),
                'usaMotaTexto' => true,
                'tipos' => collect(),
                'platosPorTipo' => collect(),
                'tablasDisponibles' => false,
            ]);
        }

        try {
            $endpoint = rtrim(trim($apiBaseUrl), '/').'/produktuak';
            $response = Http::timeout(5)->acceptJson()->get($endpoint);

            if (! $response->successful()) {
                return view('pages.menu', [
                    'categorias' => collect(),
                    'productosPorCategoria' => collect(),
                    'usaMotaTexto' => true,
                    'tipos' => collect(),
                    'platosPorTipo' => collect(),
                    'tablasDisponibles' => false,
                ]);
            }

            $data = $response->json();
            $motas = collect($data['motas'] ?? []);

            $productosPorCategoria = $motas->mapWithKeys(function ($motaGroup) {
                $key = (string) ($motaGroup['mota'] ?? 'Besteak');
                $items = collect($motaGroup['produktuak'] ?? [])
                    ->map(function ($p) {
                        $o = (object) $p;

                        if (isset($o->irudia) && is_string($o->irudia) && $o->irudia !== '') {
                            $o->irudia_url = route('external.images.show', ['filename' => $o->irudia]);
                        }

                        return $o;
                    });

                return [$key => $items];
            });

            $categorias = $motas
                ->map(fn ($motaGroup) => (string) ($motaGroup['mota'] ?? 'Besteak'))
                ->filter(fn ($mota) => $mota !== '')
                ->values()
                ->map(fn ($key) => [
                    'key' => $key,
                    'label' => $key,
                ]);

            return view('pages.menu', [
                'categorias' => $categorias,
                'productosPorCategoria' => $productosPorCategoria,
                'usaMotaTexto' => true,
                'tipos' => collect(),
                'platosPorTipo' => collect(),
                'tablasDisponibles' => (bool) ($data['tablasDisponibles'] ?? true),
            ]);
        } catch (\Throwable $e) {
            return view('pages.menu', [
                'categorias' => collect(),
                'productosPorCategoria' => collect(),
                'usaMotaTexto' => true,
                'tipos' => collect(),
                'platosPorTipo' => collect(),
                'tablasDisponibles' => false,
            ]);
        }
    }
}
