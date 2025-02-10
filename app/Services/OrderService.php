<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Fetch new orders from the API for a specific establishment.
     *
     * @param string $unitId
     * @param string $apiKey
     * @return array
     */

    public static function fetchNewOrders(string $unitId, string $apiKey): array
    {
        $url = "https://api.dev.oeda.site/api/unit/{$unitId}/order?api_key={$apiKey}&page=1&per_page=20";
        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Failed to fetch orders for unit_id: {$unitId}", ['response' => $response->body()]);

        return [];
    }
}
