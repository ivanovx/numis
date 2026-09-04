<?php

namespace App\Services;

use DeepL\DeepLClient;
use RuntimeException;

class CoinTranslationService
{
    private const TRANSLATED_FIELDS = [
        'title',
        'edge',
        'mint',
        'front_description',
        'back_description',
        'description',
    ];

    public function translateBulgarianFields(array $data): array
    {
        return $this->translateFields($data, self::TRANSLATED_FIELDS);
    }

    public function translateFields(array $data, array $fields): array
    {
        $apiKey = config('services.deepl.key');

        if (! filled($apiKey)) {
            throw new RuntimeException('DeepL API key is not configured. Add DEEPL_API_KEY to the .env file.');
        }

        $texts = [];
        foreach ($fields as $field) {
            $bulgarian = trim((string) data_get($data, $field.'.bg', ''));

            if ($bulgarian !== '') {
                $texts[$field] = $bulgarian;
            }
        }

        if ($texts === []) {
            return $data;
        }

        $client = new DeepLClient($apiKey);

        foreach (['en' => 'EN', 'de' => 'DE'] as $locale => $targetLanguage) {
            $translations = $client->translateText(array_values($texts), 'BG', $targetLanguage);

            foreach (array_keys($texts) as $index => $field) {
                $data[$field][$locale] = $translations[$index]->text;
            }
        }

        return $data;
    }
}
