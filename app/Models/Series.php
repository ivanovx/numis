<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Series::class, 'parent_id');
    }

    public function coins(): BelongsToMany
    {
        return $this->belongsToMany(Coin::class, 'coin_series');
    }

    /**
     * Flat list of all series ordered for a hierarchical <select>,
     * with a `depth` attribute so views can indent child series.
     *
     * @return \Illuminate\Support\Collection<int, Series>
     */
    public static function flatTree(): \Illuminate\Support\Collection
    {
        $all = static::orderBy('name')->get();

        $build = function ($parentId, $depth) use (&$build, $all) {
            $result = collect();

            foreach ($all->where('parent_id', $parentId) as $node) {
                $node->depth = $depth;
                $result->push($node);
                $result = $result->merge($build($node->id, $depth + 1));
            }

            return $result;
        };

        return $build(null, 0);
    }
}
