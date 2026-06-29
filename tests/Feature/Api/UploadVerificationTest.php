<?php

namespace Tests\Feature\Api;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UploadVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function worker(): User
    {
        return User::where('email', 'worker3@cyaowork.cm')->first(); // Flore, statut "pending" au départ
    }

    private function admin(): User
    {
        return User::where('email', 'admin@cyaowork.cm')->first();
    }

    public function test_depot_de_cni_met_le_profil_en_attente_puis_admin_valide(): void
    {
        $worker = $this->worker();

        // 1) Dépôt de la CNI.
        Sanctum::actingAs($worker);
        $docId = $this->post('/api/v1/worker/documents', [
            'type' => 'cni',
            'file' => UploadedFile::fake()->image('cni.jpg', 600, 400),
        ], ['Accept' => 'application/json'])
            ->assertCreated()->json('document.id');

        $this->assertDatabaseHas('documents', ['id' => $docId, 'status' => 'pending']);
        $this->assertSame('pending', $worker->workerProfile->fresh()->verification_status);

        // 2) L'admin voit la file et approuve.
        Sanctum::actingAs($this->admin());
        $this->getJson('/api/v1/admin/verifications')
            ->assertOk()->assertJsonFragment(['type' => 'cni']);

        $this->postJson("/api/v1/admin/documents/{$docId}/approve")
            ->assertOk()->assertJsonPath('status', 'approved');

        // 3) Le profil devient vérifié.
        $this->assertSame('verified', $worker->workerProfile->fresh()->verification_status);
        $this->assertTrue($worker->fresh()->is_verified);
    }

    public function test_un_non_admin_ne_voit_pas_la_file_de_verification(): void
    {
        Sanctum::actingAs($this->worker());
        $this->getJson('/api/v1/admin/verifications')->assertForbidden();
    }

    public function test_upload_photo_de_profil(): void
    {
        Sanctum::actingAs($this->worker());

        $this->post('/api/v1/worker/photo', [
            'photo' => UploadedFile::fake()->image('photo.jpg', 400, 400),
        ], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonStructure(['message', 'photo_url']);

        $this->assertTrue($this->worker()->workerProfile->fresh()->hasMedia('avatar'));
    }
}
