<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Coin extends Model
{
    use HasFactory, HasTranslatedFields;

    /** Suggested values for the "metal" field — kept as free text, these are just autocomplete hints. */
    public const METALS = ['Мед', 'Мед-никел', 'Сребро', 'Злато'];

    public const CATEGORIES = [
        'exchange',
        'commemorative',
        'collectible',
    ];

    protected $fillable = [
        'title',
        'series_id',
        'category',
        'year',
        'issue_date',
        'denomination',
        'metal',
        'quality',
        'weight',
        'diameter',
        'mintage',
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

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

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

    // --- Translatable fields: stored as {"bg":"...","en":"...","de":"..."} ---

    public function getTitleAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->encodeTranslations($value);
    }

    public function getEdgeAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setEdgeAttribute($value): void
    {
        $this->attributes['edge'] = $this->encodeTranslations($value);
    }

    public function getMintAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setMintAttribute($value): void
    {
        $this->attributes['mint'] = $this->encodeTranslations($value);
    }

    public function getFrontDescriptionAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setFrontDescriptionAttribute($value): void
    {
        $this->attributes['front_description'] = $this->encodeTranslations($value);
    }

    public function getBackDescriptionAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setBackDescriptionAttribute($value): void
    {
        $this->attributes['back_description'] = $this->encodeTranslations($value);
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setDescriptionAttribute($value): void
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
}
