<?php

namespace App\Http\Middleware;

use App\Support\IdentityOverlay;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OverlayIdentityHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! IdentityOverlay::enabled() || ! $this->shouldRewrite($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $rewritten = IdentityOverlay::rewriteHtml($content);

        if ($rewritten !== $content) {
            $response->setContent($rewritten);
        }

        return $response;
    }

    private function shouldRewrite(Request $request, Response $response): bool
    {
        if ($response instanceof StreamedResponse) {
            return false;
        }

        if ($request->is('admin', 'admin/*', 'storage/*', 'new-platform/*', 'up')) {
            return false;
        }

        $referer = (string) $request->headers->get('referer');

        if (str_contains($referer, '/admin')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('content-type'));

        return str_contains($contentType, 'text/html')
            || str_contains($contentType, 'application/json')
            || $contentType === '';
    }
}
