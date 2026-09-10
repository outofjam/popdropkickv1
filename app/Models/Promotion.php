<?php

namespace App\Models;

use App\Models\Pivots\ActivePromotionWrestler;
use App\Models\Pivots\PromotionWrestler;
use App\Traits\TracksCreatedAndUpdated;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 *
 * @property Collection|Wrestler[] $wrestlers
 * @property Collection|Wrestler[] $activeWrestlers
 */
class Promotion extends Model
{
    use HasFactory;
    use HasUuids;
    use Sluggable;
    use TracksCreatedAndUpdated;

    protected $fillable = ['name', 'abbreviation', 'country'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saved(static function (self $promotion) {
            static::forgetCache($promotion->id);
        });

        static::deleted(static function (self $promotion) {
            static::forgetCache($promotion->id);
        });
    }

    /**
     * Forget the cached PromotionService::findByIdOrSlugCached() payload
     * for this promotion. Called whenever the promotion, its championships,
     * title reigns, or wrestler roster change.
     */
    public static function forgetCache(string $promotionId): void
    {
        Cache::forget("promotion:{$promotionId}:inclInactive:0");
        Cache::forget("promotion:{$promotionId}:inclInactive:1");
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function wrestlers(): BelongsToMany
    {
        return $this->belongsToMany(Wrestler::class)
            ->using(PromotionWrestler::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function activeWrestlers(): BelongsToMany
    {
        return $this->belongsToMany(Wrestler::class, 'active_promotion_wrestler')
            ->using(ActivePromotionWrestler::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function championships(): HasMany
    {
        return $this->hasMany(Championship::class);
    }

    public function activeChampionships(): HasMany
    {
        return $this->hasMany(Championship::class)->where('active', true);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
