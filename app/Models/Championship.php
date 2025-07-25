<?php

namespace App\Models;

use App\Helpers\FilamentHelpers;
use App\Traits\TracksCreatedAndUpdated;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Forms;


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

    public static function getForm(): array
    {
        return [

            Forms\Components\TextInput::make('slug')
                ->disabled(),

            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\TextInput::make('abbreviation'),
            Forms\Components\TextInput::make('division'),
            Forms\Components\DatePicker::make('introduced_at'),
            Forms\Components\TextInput::make('weight_class'),
            Forms\Components\Toggle::make('active')
                ->required(),
            FilamentHelpers::userDisplayField('created_by', 'Created By'),
            FilamentHelpers::userDisplayField('updated_by', 'Updated By')
        ];
    }

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
