<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    /** @use HasFactory<Factory<Period>> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /** @return HasMany<Coin, $this> */
    public function coins(): HasMany
    {
        return $this->hasMany(Coin::class);
    }

    /** @return Builder<static> */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('name', 'like', $search)
                ->orWhere('slug', 'like', $search);
        });
    }
}
