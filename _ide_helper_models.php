<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property string $id
 * @property string $slug
 * @property string $promotion_id
 * @property string $name
 * @property string|null $abbreviation
 * @property string|null $division
 * @property \Illuminate\Support\Carbon|null $introduced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $weight_class
 * @property bool $active
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property-read \App\Models\TitleReign|null $currentTitleReign
 * @property-read \App\Models\Promotion $promotion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TitleReign> $titleReigns
 * @property-read int|null $title_reigns_count
 * @method static \Database\Factories\ChampionshipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereIntroducedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship whereWeightClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Championship withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 */
	class Championship extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $model_type
 * @property int|null $model_id
 * @property array<array-key, mixed> $data
 * @property array<array-key, mixed>|null $original_data
 * @property string $status
 * @property int|null $reviewer_id
 * @property string|null $reviewer_comments
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereOriginalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereReviewerComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChangeRequest whereUserId($value)
 */
	class ChangeRequest extends \Eloquent {}
}

namespace App\Models\Pivots{
/**
 * @property string $promotion_id
 * @property string $wrestler_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivePromotionWrestler whereWrestlerId($value)
 */
	class ActivePromotionWrestler extends \Eloquent {}
}

namespace App\Models\Pivots{
/**
 * @property string $promotion_id
 * @property string $wrestler_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler wherePromotionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromotionWrestler whereWrestlerId($value)
 */
	class PromotionWrestler extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property Collection|Wrestler[] $wrestlers
 * @property Collection|Wrestler[] $activeWrestlers
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $abbreviation
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Championship> $activeChampionships
 * @property-read int|null $active_championships_count
 * @property-read \App\Models\Pivots\PromotionWrestler|\App\Models\Pivots\ActivePromotionWrestler|null $pivot
 * @property-read int|null $active_wrestlers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Championship> $championships
 * @property-read int|null $championships_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\User|null $updatedBy
 * @property-read int|null $wrestlers_count
 * @method static \Database\Factories\PromotionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promotion withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 */
	class Promotion extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property string $id
 * @property string $championship_id
 * @property string $wrestler_id
 * @property int $reign_number
 * @property \Illuminate\Support\Carbon $won_on
 * @property \App\Enums\WinType|null $win_type
 * @property string|null $win_details
 * @property string|null $won_at
 * @property string|null $lost_at
 * @property \Illuminate\Support\Carbon|null $lost_on
 * @property string|null $lost_type
 * @property string|null $lost_details
 * @property int $vacated
 * @property string|null $vacancy_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $wrestler_name_id_at_win
 * @property-read \App\Models\Championship $championship
 * @property-read string $reign_length_human
 * @property-read int $reign_length_in_days
 * @property-read \App\Models\Wrestler $wrestler
 * @method static \Database\Factories\TitleReignFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereChampionshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereLostAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereLostDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereLostOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereLostType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereReignNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereVacancyReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereVacated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWinDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWinType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWonAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWonOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWrestlerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TitleReign whereWrestlerNameIdAtWin($value)
 */
	class TitleReign extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property string $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $role
 * @property int $reputation_score
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChangeRequest> $changeRequests
 * @property-read int|null $change_requests_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ChangeRequest> $reviewedChangeRequests
 * @property-read int|null $reviewed_change_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereReputationScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property string $id
 * @property string $slug
 * @property string|null $ring_name
 * @property string|null $real_name
 * @property \Illuminate\Support\Carbon|null $debut_date
 * @property string|null $country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property-read \App\Models\Pivots\PromotionWrestler|\App\Models\Pivots\ActivePromotionWrestler|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Promotion> $activePromotions
 * @property-read int|null $active_promotions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TitleReign> $activeTitleReigns
 * @property-read int|null $active_title_reigns_count
 * @property-read string|null $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WrestlerName> $names
 * @property-read int|null $names_count
 * @property-read \App\Models\WrestlerName|null $primaryName
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Promotion> $promotions
 * @property-read int|null $promotions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TitleReign> $titleReigns
 * @property-read int|null $title_reigns_count
 * @method static \Database\Factories\WrestlerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereDebutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereRealName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereRingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wrestler withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 */
	class Wrestler extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static Model|static create(array $attributes = [])
 * @method static Builder|static query()
 * @mixin Builder
 * @property string $id
 * @property string $wrestler_id
 * @property string $name
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @method static \Database\Factories\WrestlerNameFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WrestlerName whereWrestlerId($value)
 */
	class WrestlerName extends \Eloquent {}
}

