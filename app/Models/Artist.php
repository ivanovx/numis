<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    use HasFactory, HasTranslatedFields;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function coins(): BelongsToMany
    {
        return $this->belongsToMany(Coin::class, 'artist_coin');
    }

    public function getNameAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->encodeTranslations($value);
    }
}
