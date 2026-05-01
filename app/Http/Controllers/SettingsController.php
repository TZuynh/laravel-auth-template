<?php

namespace App\Http\Controllers;

use App\Services\CurrencyExchangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index(CurrencyExchangeService $currencyExchangeService)
    {
        return view('settings.index', [
            'settingsValues' => [
                'gemini_model' => (string) env('GEMINI_MODEL', config('services.gemini.model', 'gemini-2.5-flash')),
                'gemini_api_key' => (string) env('GEMINI_API_KEY', config('services.gemini.api_key', '')),
                'product_export_usd_rate' => (string) env('PRODUCT_EXPORT_USD_RATE', (string) config('services.product_export.usd_rate', '26295.55')),
            ],
            'exchangeRateInfo' => $currencyExchangeService->latestUsdToVnd(),
        ]);
    }

    public function clearCache()
    {
        Artisan::call('optimize:clear');

        return back()->with('success', __('messages.settings.cache_success'));
    }

    public function updateIntegrations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gemini_model' => ['required', 'string', 'max:120'],
            'gemini_api_key' => ['nullable', 'string', 'max:500'],
            'product_export_usd_rate' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->updateEnvValue('GEMINI_MODEL', $validated['gemini_model']);
        $this->updateEnvValue('GEMINI_API_KEY', $validated['gemini_api_key'] ?? '');
        $this->updateEnvValue('PRODUCT_EXPORT_USD_RATE', (string) $validated['product_export_usd_rate']);

        config([
            'services.gemini.model' => $validated['gemini_model'],
            'services.gemini.api_key' => $validated['gemini_api_key'] ?? '',
            'services.product_export.usd_rate' => (float) $validated['product_export_usd_rate'],
        ]);

        Artisan::call('optimize:clear');

        return back()->with('success', __('messages.settings.integration_success'));
    }

    private function updateEnvValue(string $key, string $value): void
    {
        $path = app()->environmentFilePath();
        $contents = File::exists($path) ? File::get($path) : '';
        $escapedValue = $this->normalizeEnvValue($value);
        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $contents) === 1) {
            $contents = preg_replace($pattern, "{$key}={$escapedValue}", $contents) ?? $contents;
        } else {
            $contents = rtrim($contents) . PHP_EOL . "{$key}={$escapedValue}" . PHP_EOL;
        }

        File::put($path, $contents);
    }

    private function normalizeEnvValue(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '""';
        }

        return preg_match('/\s/', $trimmed) === 1
            ? '"' . addcslashes($trimmed, '"\\') . '"'
            : $trimmed;
    }
}
