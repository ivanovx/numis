<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Coin extends Model
{
    /** @use HasFactory<Factory<Coin>> */
    use HasFactory, HasTranslatedFields;

    /** Suggested values for the "metal" field — kept as free text, these are just autocomplete hints. */
    public const METALS = ['Мед', 'Мед-никел', 'Сребро', 'Злато'];

    public const CATEGORIES = [
        'exchange',
        'commemorative',
        'collectible',
        'specimen',
        'curiosities',
    ];

    /**
     * Fields that make up a "complete" technical record. The key is either a
     * column on coins, or the pseudo-field "artists" (a relation). The value
     * is the label shown in the admin panel.
     *
     * Keep this list in sync with filledSql() below — it is the single source
     * of truth for both the per-row indicator and the grid filters/sorting.
     */
    public const COMPLETENESS_FIELDS = [
        'weight' => 'Тегло',
        'diameter' => 'Диаметър',
        'edge' => 'Гурт',
        'mintage' => 'Тираж',
        'mint' => 'Монетен двор',
        'artists' => 'Художник',
        'metal' => 'Метал, проба',
        'issue_date' => 'Дата на въвеждане',
        'km_number' => 'Каталожен номер Krause KM#',
    ];

    protected $fillable = [
        'title',
        'series_id',
        'period_id',
        'category',
        'year',
        'issue_date',
        'denomination',
        'metal',
        'quality',
        'weight',
        'diameter',
        'mintage',
        'km_number',
        'bnb_number',
        'numista_number',
        'other_references',
        'edge',
        'mint',
        'front_image',
        'front_description',
        'back_image',
        'back_description',
        'description',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    /** @return BelongsTo<Series, $this> */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /** @return BelongsTo<Period, $this> */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /** @return BelongsToMany<Artist, $this> */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_coin');
    }

    public function artistNames(): string
    {
        return $this->artists->isNotEmpty()
            ? $this->artists->pluck('name')->implode(', ')
            : '';
    }

    public function seriesName(): ?string
    {
        return $this->series?->name;
    }

    public function scopeOrderByDenomination(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $value = "LOWER(COALESCE(denomination, ''))";

        return $query
            ->orderByRaw("CASE WHEN TRIM({$value}) = '' THEN 1 ELSE 0 END")
            ->orderByRaw("
                CASE
                    WHEN {$value} LIKE '%стотин%' OR {$value} LIKE '%Стотин%' OR {$value} LIKE '%stotink%' THEN 0
                    WHEN {$value} LIKE '%лев%' OR {$value} LIKE '%Лев%' OR {$value} LIKE '%lev%' THEN 1
                    WHEN {$value} LIKE '%cent%' OR {$value} LIKE '%цент%' OR {$value} LIKE '%Цент%' THEN 2
                    WHEN {$value} LIKE '%euro%' OR {$value} LIKE '%евро%' OR {$value} LIKE '%Евро%' THEN 3
                    ELSE 4
                END {$direction}
            ")
            ->orderByRaw("CAST(denomination AS DECIMAL(12, 4)) {$direction}")
            ->orderBy('denomination', $direction);
    }

    // --- Translatable fields: stored as {"bg":"...","en":"...","de":"..."} ---

    public function getTitleAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setTitleAttribute(mixed $value): void
    {
        $this->attributes['title'] = $this->encodeTranslations($value);
    }

    public function getEdgeAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setEdgeAttribute(mixed $value): void
    {
        $this->attributes['edge'] = $this->encodeTranslations($value);
    }

    public function getMintAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setMintAttribute(mixed $value): void
    {
        $this->attributes['mint'] = $this->encodeTranslations($value);
    }

    public function getFrontDescriptionAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setFrontDescriptionAttribute(mixed $value): void
    {
        $this->attributes['front_description'] = $this->encodeTranslations($value);
    }

    public function getBackDescriptionAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setBackDescriptionAttribute(mixed $value): void
    {
        $this->attributes['back_description'] = $this->encodeTranslations($value);
    }

    public function getDescriptionAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setDescriptionAttribute(mixed $value): void
    {
        $this->attributes['description'] = $this->encodeTranslations($value);
    }

    // --- Image URLs ---

    public function getFrontImageUrlAttribute(): ?string
    {
        return $this->front_image ? Storage::disk('public')->url($this->front_image) : null;
    }

    public function getBackImageUrlAttribute(): ?string
    {
        return $this->back_image ? Storage::disk('public')->url($this->back_image) : null;
    }

    // --- Catalog references -------------------------------------------------

    public function setKmNumberAttribute(mixed $value): void
    {
        $this->attributes['km_number'] = self::clip(self::normalizeKmNumber($value), 64);
    }

    public function setBnbNumberAttribute(mixed $value): void
    {
        $this->attributes['bnb_number'] = self::clip(self::normalizeBnbNumber($value), 100);
    }

    public function setNumistaNumberAttribute(mixed $value): void
    {
        $this->attributes['numista_number'] = self::clip(self::normalizeNumistaNumber($value), 64);
    }

    public function setOtherReferencesAttribute(mixed $value): void
    {
        $value = trim((string) $value);

        $this->attributes['other_references'] = $value === '' ? null : self::clip($value, 255);
    }

    /** Keep values inside their column length so bad input never causes a database error. */
    protected static function clip(?string $value, int $length): ?string
    {
        return $value === null ? null : mb_substr($value, 0, $length);
    }

    /** "KM# 202.1", "KM 202.1", "km#202.1" -> "202.1". Letters/suffixes (e.g. "Pn12", "17a") are kept as typed. */
    public static function normalizeKmNumber(?string $value): ?string
    {
        $value = trim((string) preg_replace('/^\s*KM\s*#?\s*/i', '', trim((string) $value)));

        return $value === '' ? null : $value;
    }

    /** Accepts "N#12345", "Numista 12345", or a pasted numista.com URL; stores just the number. */
    public static function normalizeNumistaNumber(?string $value): ?string
    {
        $value = trim((string) $value);

        if (preg_match('~numista\.com/(?:[a-z]{2}/)?(?:catalogue/pieces)?(\d+)~i', $value, $matches)) {
            return $matches[1];
        }

        $value = trim((string) preg_replace('/^\s*(?:Numista|N)\s*#?\s*/i', '', $value));

        return $value === '' ? null : $value;
    }

    /**
     * Accepts the bare code ("NC_2025_9_100LV"), the code with a locale suffix
     * ("..._BG"), or a pasted bnb.bg URL; stores the canonical bare code.
     */
    public static function normalizeBnbNumber(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '/')) {
            $path = parse_url($value, PHP_URL_PATH);
            $value = basename(is_string($path) && $path !== '' ? $path : $value);
        }

        $value = (string) preg_replace('/\.html?$/i', '', $value);
        $value = (string) preg_replace('/_(BG|EN|DE)$/i', '', $value);
        $value = Str::upper(trim($value));

        return $value === '' ? null : $value;
    }

    /** Link to the official BNB page of this emission (source of the data), in the visitor's language. */
    public function bnbUrl(?string $locale = null): ?string
    {
        // Only the commemorative-emissions pages follow this URL scheme.
        if (! filled($this->bnb_number) || ! str_starts_with($this->bnb_number, 'NC_')) {
            return null;
        }

        $suffix = ($locale ?? app()->getLocale()) === 'bg' ? 'BG' : 'EN';

        return 'https://www.bnb.bg/NotesAndCoins/NACCommemorative/NACCommemorativeEmissions/'.$this->bnb_number.'_'.$suffix;
    }

    public function numistaUrl(?string $locale = null): ?string
    {
        if (! filled($this->numista_number) || ! ctype_digit((string) $this->numista_number)) {
            return null;
        }

        $subdomain = ($locale ?? app()->getLocale()) === 'de' ? 'de' : 'en';

        return 'https://'.$subdomain.'.numista.com/'.$this->numista_number;
    }

    /**
     * Catalog references that are filled in, for display. Labels are
     * translated in the view; this only carries keys, values and links.
     *
     * @return array<int, array{key: string, value: string, url: ?string}>
     */
    public function catalogReferences(?string $locale = null): array
    {
        $references = [];

        if (filled($this->km_number)) {
            $references[] = ['key' => 'km', 'value' => 'KM# '.$this->km_number, 'url' => null];
        }

        if (filled($this->numista_number)) {
            $references[] = ['key' => 'numista', 'value' => 'N# '.$this->numista_number, 'url' => $this->numistaUrl($locale)];
        }

        if (filled($this->bnb_number)) {
            $references[] = ['key' => 'bnb', 'value' => $this->bnb_number, 'url' => $this->bnbUrl($locale)];
        }

        if (filled($this->other_references)) {
            $references[] = ['key' => 'other', 'value' => $this->other_references, 'url' => null];
        }

        return $references;
    }

    public function issueDateIso(): ?string
    {
        return $this->issue_date?->format('Y-m-d');
    }

    // --- Data completeness --------------------------------------------------

    /**
     * How complete this coin's technical record is.
     *
     * @return array{filled: int, total: int, percent: int, missing: array<string, string>}
     */
    public function completeness(): array
    {
        $missing = [];

        foreach (self::COMPLETENESS_FIELDS as $field => $label) {
            if (! $this->hasCompletenessValue($field)) {
                $missing[$field] = $label;
            }
        }

        $total = count(self::COMPLETENESS_FIELDS);
        $filled = $total - count($missing);

        return [
            'filled' => $filled,
            'total' => $total,
            'percent' => (int) round($filled / $total * 100),
            'missing' => $missing,
        ];
    }

    protected function hasCompletenessValue(string $field): bool
    {
        return match ($field) {
            'artists' => $this->artists->isNotEmpty(),
            'issue_date' => $this->issue_date !== null,
            // Translated JSON fields: filled if any language has text.
            'edge', 'mint' => filled($this->$field),
            default => filled($this->getAttribute($field)),
        };
    }

    /**
     * SQL condition that is true when the field is filled. Mirrors
     * hasCompletenessValue() so the grid filters agree with the indicator.
     */
    public static function filledSql(string $field): string
    {
        return match ($field) {
            'artists' => 'EXISTS (SELECT 1 FROM artist_coin WHERE artist_coin.coin_id = coins.id)',
            // Translated fields store NULL when every language is empty (see HasTranslatedFields).
            'issue_date', 'edge', 'mint' => "coins.{$field} IS NOT NULL",
            default => "(coins.{$field} IS NOT NULL AND TRIM(coins.{$field}) <> '')",
        };
    }

    /** SQL expression: number of filled completeness fields for a coin row. */
    public static function completenessScoreSql(): string
    {
        return collect(array_keys(self::COMPLETENESS_FIELDS))
            ->map(fn (string $field) => 'CASE WHEN '.self::filledSql($field).' THEN 1 ELSE 0 END')
            ->implode(' + ');
    }

    /**
     * @param  Builder<Coin>  $query
     * @return Builder<Coin>
     */
    public function scopeMissingField(Builder $query, string $field): Builder
    {
        if (! array_key_exists($field, self::COMPLETENESS_FIELDS)) {
            return $query;
        }

        // @phpstan-ignore argument.type (SQL comes from the COMPLETENESS_FIELDS constant only, never from input)
        return $query->whereRaw('NOT ('.self::filledSql($field).')');
    }

    /**
     * @param  Builder<Coin>  $query
     * @param  'complete'|'incomplete'|string  $state
     * @return Builder<Coin>
     */
    public function scopeCompletenessState(Builder $query, string $state): Builder
    {
        $total = count(self::COMPLETENESS_FIELDS);

        // The raw SQL is built from the COMPLETENESS_FIELDS constant only (no user input).
        return match ($state) {
            // @phpstan-ignore argument.type
            'complete' => $query->whereRaw('('.self::completenessScoreSql().') = '.$total),
            // @phpstan-ignore argument.type
            'incomplete' => $query->whereRaw('('.self::completenessScoreSql().') < '.$total),
            default => $query,
        };
    }

    /**
     * @param  Builder<Coin>  $query
     * @return Builder<Coin>
     */
    public function scopeOrderByCompleteness(Builder $query, string $direction = 'asc'): Builder
    {
        // @phpstan-ignore argument.type (SQL comes from the COMPLETENESS_FIELDS constant; direction is whitelisted)
        return $query->orderByRaw('('.self::completenessScoreSql().') '.(strtolower($direction) === 'desc' ? 'DESC' : 'ASC'));
    }

    /**
     * Catalog-wide completeness summary (one query) for the admin dashboard.
     *
     * @return array{total: int, average_percent: int, complete: int, missing: array<string, int>}
     */
    public static function completenessReport(): array
    {
        $fields = array_keys(self::COMPLETENESS_FIELDS);
        $fieldCount = count($fields);
        $score = self::completenessScoreSql();

        $selects = [
            'COUNT(*) AS total',
            "COALESCE(SUM({$score}), 0) AS score_sum",
            "COALESCE(SUM(CASE WHEN ({$score}) = {$fieldCount} THEN 1 ELSE 0 END), 0) AS complete_count",
        ];

        foreach ($fields as $field) {
            $selects[] = 'COALESCE(SUM(CASE WHEN '.self::filledSql($field)." THEN 0 ELSE 1 END), 0) AS missing_{$field}";
        }

        // @phpstan-ignore argument.type (every part is built from the COMPLETENESS_FIELDS constant, never from input)
        $row = DB::table('coins')->selectRaw(implode(', ', $selects))->first();

        $total = (int) $row->total;

        return [
            'total' => $total,
            'average_percent' => $total > 0 ? (int) round($row->score_sum / ($total * $fieldCount) * 100) : 0,
            'complete' => (int) $row->complete_count,
            'missing' => collect($fields)->mapWithKeys(fn (string $field) => [$field => (int) $row->{"missing_{$field}"}])->all(),
        ];
    }

    /**
     * Extract the base metal/fineness from the full metal string, stripping
     * any variant description (partial gilding, oxidation, etc.).
     * "Ag 925/1000 с нанесено частично позлатяване" -> "Ag 925/1000"
     */
    public static function baseMetal(string $metal): string
    {
        if (preg_match('/^([A-Za-zА-Яа-я\-]+ ?\d{3}\/1000)/u', $metal, $m)) {
            return trim($m[1]);
        }

        $parts = preg_split('/\s+с\s+|\s+with\s+/u', $metal, 2) ?: [$metal];

        return trim($parts[0]);
    }
}
