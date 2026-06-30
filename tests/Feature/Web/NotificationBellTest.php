<?php

namespace Tests\Feature\Web;

use App\Models\Application;
use App\Notifications\NewApplicationReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_la_cloche_est_rendue_sur_le_dashboard(): void
    {
        $worker = User::where('email', 'worker1@cyaowork.cm')->first();

        $this->actingAs($worker)->get(route('worker.dashboard'))
            ->assertOk()->assertSee('notif-bell');
    }

    public function test_les_notifications_sont_diffusees_en_temps_reel(): void
    {
        $employer = User::where('email', 'employeur@cyaowork.cm')->first();
        $via = (new NewApplicationReceived(Application::first()))->via($employer);

        $this->assertContains('broadcast', $via);
        $this->assertContains('database', $via);
    }

    public function test_tout_marquer_comme_lu(): void
    {
        $worker = User::where('email', 'worker1@cyaowork.cm')->first();
        DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => $worker->getMorphClass(),
            'notifiable_id' => $worker->id,
            'data' => ['title' => 'Test', 'message' => 'x'],
        ]);
        $this->assertSame(1, $worker->unreadNotifications()->count());

        $this->actingAs($worker)->post(route('notifications.read'))->assertRedirect();

        $this->assertSame(0, $worker->fresh()->unreadNotifications()->count());
    }
}
