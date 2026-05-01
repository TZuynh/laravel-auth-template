<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyExchangeService
{
    public function latestUsdToVnd(): ?array
    {
        return Cache::remember('currency:usd-vnd:latest', now()->addHour(), function () {
            $response = Http::timeout(10)->acceptJson()->get('https://open.er-api.com/v6/latest/USD');

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            $rate = data_get($data, 'rates.VND');

            if (!is_numeric($rate)) {
                return null;
            }

            return [
                'usd_to_vnd' => (float) $rate,
                'provider' => data_get($data, 'provider', 'https://www.exchangerate-api.com'),
                'documentation' => data_get($data, 'documentation', 'https://www.exchangerate-api.com/docs/free'),
                'last_update_utc' => data_get($data, 'time_last_update_utc'),
                'next_update_utc' => data_get($data, 'time_next_update_utc'),
            ];
        });
    }
}
