<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    public function test_only_admin_and_moderator_can_review(): void
    {
        $this->assertTrue((new User(['role' => 'admin']))->canReview());
        $this->assertTrue((new User(['role' => 'moderator']))->canReview());
        $this->assertFalse((new User(['role' => 'trusted']))->canReview());
        $this->assertFalse((new User(['role' => 'user']))->canReview());
    }

    public function test_admin_can_auto_approve_any_action(): void
    {
        $admin = new User(['role' => 'admin']);

        $this->assertTrue($admin->canAutoApprove('wrestler_create'));
        $this->assertTrue($admin->canAutoApprove('championship_create'));
        $this->assertTrue($admin->canAutoApprove('title_reign_create'));
    }

    public function test_moderator_cannot_auto_approve_championship_and_title_reign_lineage_changes(): void
    {
        $moderator = new User(['role' => 'moderator']);

        $this->assertFalse($moderator->canAutoApprove('championship_create'));
        $this->assertFalse($moderator->canAutoApprove('title_reign_create'));
        $this->assertFalse($moderator->canAutoApprove('title_reign_update'));

        $this->assertTrue($moderator->canAutoApprove('wrestler_create'));
        $this->assertTrue($moderator->canAutoApprove('wrestler_update'));
    }

    public function test_trusted_or_high_reputation_users_can_only_auto_approve_allowed_minor_actions(): void
    {
        $trusted = new User(['role' => 'trusted', 'reputation_score' => 0]);

        $this->assertTrue($trusted->canAutoApprove('wrestler_update'));
        $this->assertTrue($trusted->canAutoApprove('wrestler_alias_create'));
        $this->assertTrue($trusted->canAutoApprove('promotion_update'));
        $this->assertFalse($trusted->canAutoApprove('wrestler_create'));

        $highReputation = new User(['role' => 'user', 'reputation_score' => 100]);

        $this->assertTrue($highReputation->canAutoApprove('wrestler_update'));
        $this->assertFalse($highReputation->canAutoApprove('wrestler_create'));
    }

    public function test_regular_user_cannot_auto_approve_anything(): void
    {
        $user = new User(['role' => 'user', 'reputation_score' => 0]);

        $this->assertFalse($user->canAutoApprove('wrestler_create'));
        $this->assertFalse($user->canAutoApprove('wrestler_update'));
        $this->assertFalse($user->canAutoApprove('promotion_update'));
    }
}
