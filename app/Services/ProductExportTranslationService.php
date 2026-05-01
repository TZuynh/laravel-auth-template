<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProductExportTranslationService
{
    private const CACHE_VERSION = 'v3';

    public function translateField(string $field, ?string $value, string $targetLocale): ?string
    {
        $targetLocale = in_array($targetLocale, ['vi', 'en'], true) ? $targetLocale : 'en';
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $cacheKey = 'product-export-translation:' . self::CACHE_VERSION . ':' . $targetLocale . ':' . $field . ':' . sha1($text);

        return Cache::remember($cacheKey, now()->addDays(45), function () use ($field, $text, $targetLocale) {
            $apiKey = (string) config('services.gemini.api_key', '');
            $model = (string) config('services.gemini.model', 'gemini-2.5-flash');

            if ($apiKey === '') {
                return $text;
            }

            $instruction = implode("\n", [
                'You translate e-commerce product fields.',
                'Return plain text only.',
                'Do not wrap the result in JSON, markdown, or quotes.',
                'Do not add notes or explanations.',
                'Preserve brand names, SKUs, and model codes.',
                $field === 'name'
                    ? 'Translate the product name into fluent marketplace language.'
                    : 'Translate the SEO title into fluent marketplace language.',
                $targetLocale === 'en'
                    ? 'Translate all visible Vietnamese content into natural English. Output must be fully English unless a token is a brand, SKU, or proper noun.'
                    : 'Translate all visible English content into natural Vietnamese. Output must be fully Vietnamese unless a token is a brand, SKU, or proper noun.',
                '',
                'Field: ' . $field,
                'Target locale: ' . $targetLocale,
                'Input:',
                $text,
            ]);

            try {
                $response = Http::timeout(30)
                    ->acceptJson()
                    ->contentType('application/json')
                    ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [[
                            'role' => 'user',
                            'parts' => [
                                ['text' => $instruction],
                            ],
                        ]],
                    ]);
            } catch (\Throwable) {
                return $text;
            }

            if (!$response->ok()) {
                return $text;
            }

            $translated = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));

            if ($translated === '') {
                return $text;
            }

            return preg_replace('/^```[\w-]*\s*|\s*```$/u', '', $translated) ?: $text;
        });
    }

    public function translateFields(array $fields, string $targetLocale): array
    {
        $targetLocale = in_array($targetLocale, ['vi', 'en'], true) ? $targetLocale : 'en';
        $normalized = $this->normalizeFields($fields);

        if ($normalized === []) {
            return [];
        }

        $translated = [];

        foreach ($normalized as $field => $value) {
            $translated[$field] = $this->translateField($field, $value, $targetLocale) ?? $value;
        }

        return $translated;
    }

    private function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $text = trim((string) $value);
            if ($text === '') {
                continue;
            }

            $normalized[$key] = $text;
        }

        return $normalized;
    }
}
