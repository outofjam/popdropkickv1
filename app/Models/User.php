<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 *
 * @mixin Builder
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'reputation_score'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function reviewedChangeRequests(): HasMany
    {
        return $this->hasMany(ChangeRequest::class, 'reviewer_id');
    }

    /**
     * Check if user can auto-approve certain types of changes
     */
    public function canAutoApprove(string $action): bool
    {
        // Admins can auto-approve everything
        if ($this->role === 'admin') {
            return true;
        }

        // Moderators can auto-approve most things
        if ($this->role === 'moderator') {
            // But maybe not championship lineage changes
            $restrictedActions = ['championship_create', 'title_reign_create', 'title_reign_update'];
            return !in_array($action, $restrictedActions);
        }

        // Trusted users based on reputation
        if ($this->role === 'trusted' || $this->reputation_score >= 100) {
            // Can auto-approve minor edits only
            $allowedActions = [
                'wrestler_update', // profile updates
                'wrestler_alias_create', // adding ring names
                'promotion_update' // minor promotion details
            ];
            return in_array($action, $allowedActions);
        }

        // Regular users need everything approved
        return false;
    }

    /**
     * Check if user can review change requests
     */
    public function canReview(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    /**
     * Get user's approval statistics
     */
    public function getApprovalStats(): array
    {
        return [
            'submitted' => $this->changeRequests()->count(),
            'approved' => $this->changeRequests()->where('status', 'approved')->count(),
            'rejected' => $this->changeRequests()->where('status', 'rejected')->count(),
            'pending' => $this->changeRequests()->where('status', 'pending')->count(),
            'approval_rate' => $this->calculateApprovalRate(),
        ];
    }

    private function calculateApprovalRate(): float
    {
        $total = $this->changeRequests()->whereIn('status', ['approved', 'rejected'])->count();
        if ($total === 0) {
            return 0;
        }

        $approved = $this->changeRequests()->where('status', 'approved')->count();
        return round(($approved / $total) * 100, 1);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
