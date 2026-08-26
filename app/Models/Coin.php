<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Coin extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'year',
        'denomination',
        'metal',
        'diameter',
        'front_image',
        'back_image',
        'description',
    ];

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'coin_series');
    }

    public function seriesNames(): string
    {
        return $this->series->isNotEmpty()
            ? $this->series->pluck('name')->implode(', ')
            : 'None';
    }

    public function getFrontImageUrlAttribute(): ?string
    {
        return $this->front_image ? Storage::disk('public')->url($this->front_image) : null;
    }

    public function getBackImageUrlAttribute(): ?string
    {
        return $this->back_image ? Storage::disk('public')->url($this->back_image) : null;
    }
}
