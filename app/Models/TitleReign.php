<?php

namespace App\Models;

use App\Enums\WinType;
use App\Traits\TracksCreatedAndUpdated;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class TitleReign extends Model
{
    use HasFactory;
    use HasUuids;
    use TracksCreatedAndUpdated;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'championship_id',
        'wrestler_id',
        'won_on',
        'won_at',
        'lost_on',
        'lost_at',
        'win_type',
        'reign_number',
        'wrestler_name_id_at_win'
    ];

    protected $casts = [
        'won_on' => 'date',
        'lost_on' => 'date',
        'win_type' => WinType::class,
    ];

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }

//    public function wrestler(): BelongsTo
//    {
//        return $this->belongsTo(Wrestler::class);
//    }

    // In TitleReign model
    public function getReignLengthInDaysAttribute(): int
    {
        return $this->won_on->diffInDays($this->lost_on ?? now());
    }

    /**
     * @throws Exception
     */
    public function getReignLengthHumanAttribute(): string
    {
        $start = $this->won_on;
        $end = $this->lost_on ?? now();

        $diff = $start->diff($end);

        return CarbonInterval::years($diff->y)
            ->months($diff->m)
            ->days($diff->d)
            ->forHumans([
                'short' => true,
                'parts' => 2, // only show 2 units max: "4y 10mo"
                'join' => true, // no commas
            ]);
    }

    // app/Models/TitleReign.php
    public function aliasAtWin(): BelongsTo
    {
        return $this->belongsTo(WrestlerName::class, 'wrestler_name_id_at_win');
    }

    public function wrestler(): BelongsTo
    {
        return $this->belongsTo(Wrestler::class, 'wrestler_id');
    }

    // app/Models/TitleReign.php


    /**
     * The wrestler we should display for this reign.
     * 1) aliasAtWin->wrestler (preferred)
     * 2) wrestler (if column exists and set)
     * 3) null
     */
    public function getResolvedWrestlerAttribute(): ?Wrestler
    {
        if ($this->relationLoaded('aliasAtWin') && $this->aliasAtWin) {
            return $this->aliasAtWin->wrestler ?? null;
        }
        if ($this->aliasAtWin) {
            return $this->aliasAtWin->wrestler()->first();
        }
        // fallback if you still have wrestler_id
        if ($this->relationLoaded('wrestler')) {
            return $this->wrestler;
        }
        return $this->wrestler()->first();
    }

    /**
     * The alias record we should display:
     * 1) aliasAtWin (preferred)
     * 2) resolved wrestler's primaryName
     */
    public function getResolvedAliasAtWinAttribute(): ?WrestlerName
    {
        if ($this->aliasAtWin) {
            return $this->aliasAtWin;
        }
        $w = $this->resolved_wrestler;
        if ($w) {
            return $w->primaryName()->first();
        }
        return null;
    }

    /**
     * Convenience string: the name we should show.
     */
    public function getResolvedDisplayNameAtWinAttribute(): ?string
    {
        return $this->resolved_alias_at_win?->name;
    }



}
