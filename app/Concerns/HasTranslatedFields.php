<?php

namespace App\Concerns;

/**
 * Backs a model attribute with a JSON blob of per-locale strings, e.g.
 * {"bg":"...","en":"...","de":"..."}. Reading the attribute normally
 * (e.g. $coin->title) returns the current app locale's value, falling
 * back to the app's fallback locale, then to whichever locale has a
 * non-empty value. Writing accepts either an array (['en' => '...'])
 * or a plain string (stored as-is for backward compatibility).
 */
trait HasTranslatedFields
{
    protected function translatedValue(?string $json): ?string
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            // Not JSON — treat as a plain legacy string.
            return $json;
        }

        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        if (! empty($decoded[$locale])) {
            return $decoded[$locale];
        }

        if (! empty($decoded[$fallback])) {
            return $decoded[$fallback];
        }

        foreach ($decoded as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Raw value for one specific locale — used by admin forms to
     * pre-fill each language's input separately.
     */
    public function translation(string $field, string $locale): ?string
    {
        $raw = $this->attributes[$field] ?? null;

        if ($raw === null) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? ($decoded[$locale] ?? null) : null;
    }

    protected function encodeTranslations($value): ?string
    {
        if (is_array($value)) {
            $filtered = array_filter($value, fn ($v) => $v !== null && $v !== '');

            return $filtered ? json_encode($value, JSON_UNESCAPED_UNICODE) : null;
        }

        return $value;
    }
}
