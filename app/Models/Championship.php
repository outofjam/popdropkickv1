<?php

namespace App\Models;

use App\Traits\TracksCreatedAndUpdated;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class Championship extends Model
{
    use HasFactory, HasUuids, Sluggable, TracksCreatedAndUpdated;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'promotion_id',
        'introduced_at',
        'weight_class',
        'active',
    ];

    protected $casts = [
        'introduced_at' => 'date',
        'active' => 'boolean',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => false, // lock slug after creation
            ],
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function titleReigns(): HasMany
    {
        return $this->hasMany(TitleReign::class);
    }

    public function currentTitleReign()
    {
        return $this->hasOne(TitleReign::class)
            ->whereNull('lost_on')
            ->latest('won_on'); // in case multiple, get the latest started reign
    }
}
