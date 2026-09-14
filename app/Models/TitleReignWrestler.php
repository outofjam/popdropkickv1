<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One wrestler's participation in a title reign, with their own
 * alias-at-win and their own reign_number for that championship. A reign
 * with N participants (tag team, trios, stable) has N of these rows; a
 * singles reign has exactly one.
 *
 * The route-wrestler row of every reign is still mirrored automatically
 * from TitleReign::wrestler_id/wrestler_name_id_at_win (see
 * TitleReign::syncPrimaryParticipant()); TitleReignService writes any
 * additional co-champions directly to this table.
 */
class TitleReignWrestler extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title_reign_id',
        'wrestler_id',
        'wrestler_name_id_at_win',
        'reign_number',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function titleReign(): BelongsTo
    {
        return $this->belongsTo(TitleReign::class);
    }

    public function wrestler(): BelongsTo
    {
        return $this->belongsTo(Wrestler::class);
    }

    public function aliasAtWin(): BelongsTo
    {
        return $this->belongsTo(WrestlerName::class, 'wrestler_name_id_at_win');
    }

    /**
     * The alias we should display: alias-at-win, or the wrestler's
     * primary name if none was captured.
     */
    public function getResolvedDisplayNameAttribute(): ?string
    {
        return $this->aliasAtWin?->name ?? $this->wrestler?->primaryName?->name;
    }
}
