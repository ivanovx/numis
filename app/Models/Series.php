<?php

namespace App\Models;

use App\Concerns\HasTranslatedFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use HasFactory, HasTranslatedFields;

    protected $table = 'series';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function coins(): HasMany
    {
        return $this->hasMany(Coin::class);
    }

    // name is stored as {"bg":"...","en":"...","de":"..."}
    public function getNameAttribute($value): ?string
    {
        return $this->translatedValue($value);
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->encodeTranslations($value);
    }

    /**
     * All series ordered for a <select>. Sorted by slug at the DB level
     * (name is JSON, not sortable in SQL), then by the current locale's
     * translated name.
     *
     * @return \Illuminate\Support\Collection<int, Series>
     */
    public static function forSelect(): \Illuminate\Support\Collection
    {
        return static::orderBy('slug')->get()->sortBy('name')->values();
    }
}
