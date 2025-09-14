<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class ChangeRequest extends Model
{


    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'data',
        'original_data',
        'status',
        'reviewer_id',
        'reviewer_comments',
        'reviewed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'original_data' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the model being changed (polymorphic-ish)
     */
    public function getTargetModel()
    {
        if (!$this->model_id) {
            return null;
        }

        $modelClass = match ($this->model_type) {
            'wrestler' => Wrestler::class,
            'championship' => Championship::class,
            'title_reign' => TitleReign::class,
            'promotion' => Promotion::class,
            default => null,
        };

        if ($modelClass) {
            return $modelClass::find($this->model_id);
        }
        return null;
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if this request can be reviewed
     */
    public function canBeReviewed(): bool
    {
        return $this->status === 'pending';
    }
}
