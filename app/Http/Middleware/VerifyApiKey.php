<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.external_api.key');

        if (empty($configuredKey)) {
            return response()->json([
                'response_code'    => '503',
                'response_message' => 'API belum dikonfigurasi. Set EXTERNAL_API_KEY di file .env.',
            ], 503);
        }

        $providedKey = $request->header('X-API-Key');

        if (!$providedKey || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'response_code'    => '401',
                'response_message' => 'API key tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
