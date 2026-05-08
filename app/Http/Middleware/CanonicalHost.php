<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = rtrim((string) config('app.url'), '/');
        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);
        $host = $request->getHost();

        if (
            $canonicalUrl !== ''
            && $canonicalHost
            && in_array($request->method(), ['GET', 'HEAD'], true)
            && in_array($host, ['192.168.20.5', '192.168.10.5'], true)
            && $request->ip() !== '192.168.10.5'
        ) {
            return redirect()->away($canonicalUrl.$request->getRequestUri());
        }

        return $next($request);
    }
}
