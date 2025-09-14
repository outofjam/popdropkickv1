<?php

namespace App\Models;

use App\Models\Pivots\ActivePromotionWrestler;
use App\Models\Pivots\PromotionWrestler;
use App\Traits\TracksCreatedAndUpdated;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class Wrestler extends Model
{
    use HasFactory;
    use HasUuids;
    use Sluggable;
    use TracksCreatedAndUpdated;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'real_name',
        'debut_date',
        'country',

        'championship_id',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => null, // no automatic slug generation
                'onUpdate' => false,
            ],
        ];
    }

    protected static function booted(): void
    {
        static::created(static function (self $wrestler) {
            if (! empty($wrestler->slug)) {
                return; // slug already set, skip
            }

            $primaryName = $wrestler->primaryName()->first();

            if ($primaryName) {
                $slugBase = Str::slug($primaryName->name);
                $slug = static::generateUniqueSlug($slugBase);

                $wrestler->slug = $slug;
                $wrestler->saveQuietly();
            }
        });
    }

    public static function generateUniqueSlug(string $baseSlug, int $counter = 0): string
    {
        $slug = $counter > 0 ? $baseSlug.'-'.$counter : $baseSlug;

        if (static::where('slug', $slug)->exists()) {
            return static::generateUniqueSlug($baseSlug, $counter + 1);
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        // Assuming you store slug in a 'slug' column or generate it
        // Return 'slug' to bind by slug; if you want to support both, override binding below
        return 'slug';
    }

    protected $casts = [
        'debut_date' => 'date', // Cast to Carbon automatically
    ];

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)
            ->using(PromotionWrestler::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function activePromotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'active_promotion_wrestler')
            ->using(ActivePromotionWrestler::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function names(): self|HasMany
    {
        return $this->hasMany(WrestlerName::class);
    }

    public function primaryName(): HasOne
    {
        return $this->hasOne(WrestlerName::class)->where('is_primary', true);
    }

    // Optional: get primary name as an attribute for easy access
    public function getNameAttribute(): ?string
    {
        return $this->primaryName?->name ?? null;
    }

    public function titleReigns(): HasMany
    {
        return $this->hasMany(TitleReign::class);
    }

    public function activeTitleReigns(): HasMany
    {
        return $this->hasMany(TitleReign::class)->whereNull('lost_on');
    }

    public function deactivatePromotion(Promotion $promotion): void
    {
        // they have left the promotion
        $this->activePromotions()->detach($promotion->id);
    }

    public function deactivateAllPromotions(): void
    {
        // used for mark as retired or something of that short
        $this->activePromotions()->detach();
    }
}
