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
    use HasFactory, HasUuids, TracksCreatedAndUpdated;

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

    public function wrestler(): BelongsTo
    {
        return $this->belongsTo(Wrestler::class);
    }

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
}
