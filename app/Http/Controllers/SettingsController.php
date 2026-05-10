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
        $settingsValues = [
            'site_name' => (string) env('SITE_NAME', 'Owl Agency'),
            'site_hotline' => (string) env('SITE_HOTLINE', ''),
            'support_email' => (string) env('SUPPORT_EMAIL', ''),
            'office_address' => (string) env('OFFICE_ADDRESS', ''),
            'seo_description' => (string) env('SEO_DESCRIPTION', ''),
            'gemini_model' => (string) env('GEMINI_MODEL', config('services.gemini.model', 'gemini-2.5-flash')),
            'gemini_api_key' => (string) env('GEMINI_API_KEY', config('services.gemini.api_key', '')),
            'google_maps_api_key' => (string) env('GOOGLE_MAPS_API_KEY', ''),
            'bank_name' => (string) env('BANK_NAME', 'Vietcombank (VCB)'),
            'bank_account' => (string) env('BANK_ACCOUNT', ''),
            'bank_holder' => (string) env('BANK_HOLDER', ''),
            'product_export_usd_rate' => (string) env('PRODUCT_EXPORT_USD_RATE', (string) config('services.product_export.usd_rate', '26295.55')),
            'smtp_host' => (string) env('SMTP_HOST', 'smtp.gmail.com'),
            'smtp_port' => (string) env('SMTP_PORT', '587'),
            'smtp_username' => (string) env('SMTP_USERNAME', ''),
            'smtp_password' => (string) env('SMTP_PASSWORD', ''),
            'smtp_from_name' => (string) env('SMTP_FROM_NAME', 'Owl Agency'),
            'smtp_from_email' => (string) env('SMTP_FROM_EMAIL', ''),
            'session_lifetime' => (string) env('SESSION_LIFETIME', '60'),
            'lock_ip_after' => (string) env('LOCK_IP_AFTER', '5'),
            'two_factor_required' => (bool) env('TWO_FACTOR_REQUIRED', false),
            'backup_enabled' => (bool) env('BACKUP_ENABLED', false),
        ];

        return view('settings.index', [
            'settingsValues' => $settingsValues,
            'vietQrPreview' => [
                'bank_code' => $this->resolveVietQrBankCode($settingsValues['bank_name']),
                'image_url' => $this->vietQrImageUrl($settingsValues['bank_name'], $settingsValues['bank_account'], $settingsValues['bank_holder']),
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
            'site_name' => ['nullable', 'string', 'max:160'],
            'site_hotline' => ['nullable', 'string', 'max:80'],
            'support_email' => ['nullable', 'string', 'max:160'],
            'office_address' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'google_maps_api_key' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:160'],
            'bank_account' => ['nullable', 'string', 'max:80'],
            'bank_holder' => ['nullable', 'string', 'max:160'],
            'smtp_host' => ['nullable', 'string', 'max:160'],
            'smtp_port' => ['nullable', 'string', 'max:12'],
            'smtp_username' => ['nullable', 'string', 'max:160'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:160'],
            'smtp_from_email' => ['nullable', 'string', 'max:160'],
            'session_lifetime' => ['nullable', 'integer', 'min:5', 'max:10080'],
            'lock_ip_after' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $envMap = [
            'SITE_NAME' => 'site_name',
            'SITE_HOTLINE' => 'site_hotline',
            'SUPPORT_EMAIL' => 'support_email',
            'OFFICE_ADDRESS' => 'office_address',
            'SEO_DESCRIPTION' => 'seo_description',
            'GEMINI_MODEL' => 'gemini_model',
            'GEMINI_API_KEY' => 'gemini_api_key',
            'GOOGLE_MAPS_API_KEY' => 'google_maps_api_key',
            'BANK_NAME' => 'bank_name',
            'BANK_ACCOUNT' => 'bank_account',
            'BANK_HOLDER' => 'bank_holder',
            'PRODUCT_EXPORT_USD_RATE' => 'product_export_usd_rate',
            'SMTP_HOST' => 'smtp_host',
            'SMTP_PORT' => 'smtp_port',
            'SMTP_USERNAME' => 'smtp_username',
            'SMTP_PASSWORD' => 'smtp_password',
            'SMTP_FROM_NAME' => 'smtp_from_name',
            'SMTP_FROM_EMAIL' => 'smtp_from_email',
            'SESSION_LIFETIME' => 'session_lifetime',
            'LOCK_IP_AFTER' => 'lock_ip_after',
        ];

        foreach ($envMap as $envKey => $requestKey) {
            $this->updateEnvValue($envKey, (string) ($validated[$requestKey] ?? ''));
        }

        $this->updateEnvValue('TWO_FACTOR_REQUIRED', $request->boolean('two_factor_required') ? 'true' : 'false');
        $this->updateEnvValue('BACKUP_ENABLED', $request->boolean('backup_enabled') ? 'true' : 'false');

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

    private function resolveVietQrBankCode(string $bankName): string
    {
        $normalized = mb_strtolower($bankName);

        return match (true) {
            str_contains($normalized, 'mb') || str_contains($normalized, 'military') => 'MB',
            str_contains($normalized, 'vietcombank') || str_contains($normalized, 'vcb') => 'VCB',
            str_contains($normalized, 'techcombank') || str_contains($normalized, 'tcb') => 'TCB',
            str_contains($normalized, 'vietinbank') || str_contains($normalized, 'ctg') => 'ICB',
            str_contains($normalized, 'bidv') => 'BIDV',
            str_contains($normalized, 'acb') => 'ACB',
            str_contains($normalized, 'sacombank') || str_contains($normalized, 'stb') => 'STB',
            default => 'VCB',
        };
    }

    private function vietQrImageUrl(string $bankName, string $accountNumber, string $accountName): string
    {
        $bankCode = $this->resolveVietQrBankCode($bankName);
        $accountNumber = preg_replace('/\D+/', '', $accountNumber) ?: '0000000000';

        return sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=0&addInfo=%s&accountName=%s',
            rawurlencode($bankCode),
            rawurlencode($accountNumber),
            rawurlencode('Thanh toan don hang'),
            rawurlencode($accountName !== '' ? $accountName : 'NGUYEN VAN A')
        );
    }
}
