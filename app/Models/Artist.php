<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    /** @use HasFactory<Factory<Artist>> */
    use HasFactory, HasTranslatedFields;

    protected $fillable = [
        'name',
        'slug',
    ];

    /** @return BelongsToMany<Coin, $this> */
    public function coins(): BelongsToMany
    {
        return $this->belongsToMany(Coin::class, 'artist_coin');
    }

    public function getNameAttribute(mixed $value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setNameAttribute(mixed $value): void
    {
        $this->attributes['name'] = $this->encodeTranslations($value);
    }
}
