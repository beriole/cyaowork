<?php

namespace Tests\Feature\Web;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@cyaowork.cm')->first();
    }

    private function flaggedReview(): Review
    {
        return Review::create([
            'reviewer_id' => User::where('email', 'employeur@cyaowork.cm')->value('id'),
            'reviewee_id' => User::where('email', 'worker1@cyaowork.cm')->value('id'),
            'rating' => 1, 'comment' => 'Propos déplacés', 'is_flagged' => true,
        ]);
    }

    public function test_admin_ignore_un_signalement(): void
    {
        $review = $this->flaggedReview();

        $this->actingAs($this->admin())
            ->post(route('admin.reports.ignore', $review))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertFalse((bool) $review->fresh()->is_flagged);
    }

    public function test_admin_sanctionne_supprime_l_avis(): void
    {
        $review = $this->flaggedReview();

        $this->actingAs($this->admin())
            ->post(route('admin.reports.sanction', $review))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_un_non_admin_ne_peut_pas_moderer(): void
    {
        $worker = User::where('email', 'worker1@cyaowork.cm')->first();

        $this->actingAs($worker)
            ->post(route('admin.reports.ignore', $this->flaggedReview()))
            ->assertForbidden();
    }
}
