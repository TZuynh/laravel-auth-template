<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProductExportTranslationService
{
    private const CACHE_VERSION = 'v4';

    public function translateField(string $field, ?string $value, string $targetLocale): ?string
    {
        $translated = $this->translateFields([$field => $value], $targetLocale);

        return $translated[$field] ?? null;
    }

    public function translateFields(array $fields, string $targetLocale): array
    {
        $targetLocale = in_array($targetLocale, ['vi', 'en'], true) ? $targetLocale : 'en';
        $normalized = $this->normalizeFields($fields);

        if ($normalized === []) {
            return [];
        }

        if (trim((string) config('services.gemini.api_key', '')) === '') {
            return $normalized;
        }

        $translated = [];
        $missing = [];

        foreach ($normalized as $field => $value) {
            $cacheKey = $this->cacheKey($field, $value, $targetLocale);

            if (Cache::has($cacheKey)) {
                $translated[$field] = (string) Cache::get($cacheKey);
                continue;
            }

            $missing[$field] = $value;
        }

        if ($missing !== []) {
            $freshTranslations = $this->requestTranslations($missing, $targetLocale);

            foreach ($missing as $field => $value) {
                $candidate = trim((string) ($freshTranslations[$field] ?? $value));
                $translated[$field] = $candidate !== '' ? $candidate : $value;

                Cache::put(
                    $this->cacheKey($field, $value, $targetLocale),
                    $translated[$field],
                    now()->addDays(45)
                );
            }
        }

        return array_replace($normalized, $translated);
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

    private function cacheKey(string $field, string $value, string $targetLocale): string
    {
        return 'product-export-translation:' . self::CACHE_VERSION . ':' . $targetLocale . ':' . $field . ':' . sha1($value);
    }

    private function requestTranslations(array $fields, string $targetLocale): array
    {
        $apiKey = trim((string) config('services.gemini.api_key', ''));
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $timeout = max(1, (int) config('services.gemini.timeout', 8));
        $connectTimeout = max(1, (int) config('services.gemini.connect_timeout', 3));

        if ($apiKey === '' || $model === '') {
            return $fields;
        }

        $instruction = implode("\n", [
            'You translate e-commerce product export fields.',
            'Return only a valid JSON object with exactly the same keys as the input object.',
            'Each JSON value must be a string.',
            'Do not wrap the JSON in markdown or add explanations.',
            'Translate every Vietnamese word or phrase into natural English when target_locale is en.',
            'Translate every English word or phrase into natural Vietnamese when target_locale is vi.',
            'If a value is already in the target language, return it unchanged.',
            'Preserve SKUs, URLs, brand names, model codes, numbers, punctuation, and separators such as | or comma.',
            'For product names, write fluent marketplace-ready copy without adding facts that are not present.',
            '',
            'target_locale: ' . $targetLocale,
            'input_json:',
            json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        try {
            $response = Http::connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->acceptJson()
                ->contentType('application/json')
                ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [
                            ['text' => $instruction],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ],
                ]);
        } catch (\Throwable) {
            return $fields;
        }

        if (!$response->ok()) {
            return $fields;
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));
        $decoded = $this->decodeTranslationJson($text);

        if ($decoded === []) {
            return $fields;
        }

        $translations = [];
        foreach ($fields as $field => $value) {
            $candidate = trim((string) ($decoded[$field] ?? $value));
            $translations[$field] = $candidate !== '' ? $candidate : $value;
        }

        return $translations;
    }

    private function decodeTranslationJson(string $text): array
    {
        $cleaned = trim($text);
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/iu', '', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned);

        $decoded = json_decode($cleaned, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($cleaned, '{');
        $end = strrpos($cleaned, '}');

        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $decoded = json_decode(substr($cleaned, $start, $end - $start + 1), true);

        return is_array($decoded) ? $decoded : [];
    }
}
