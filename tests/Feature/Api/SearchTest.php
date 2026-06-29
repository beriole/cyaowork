<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_recherche_de_travailleurs_par_categorie(): void
    {
        $menage = Category::where('slug', 'menage')->first();

        $res = $this->getJson("/api/v1/workers?category={$menage->id}")->assertOk();

        $this->assertNotEmpty($res->json('data'));
        foreach ($res->json('data') as $w) {
            $this->assertSame('Aide ménagère', $w['headline']);
        }
    }

    public function test_recherche_de_travailleurs_par_ville(): void
    {
        $res = $this->getJson('/api/v1/workers?city=Douala')->assertOk();

        $this->assertNotEmpty($res->json('data'));
        foreach ($res->json('data') as $w) {
            $this->assertSame('Douala', $w['city']);
        }
    }

    public function test_filtre_verifies_uniquement(): void
    {
        $res = $this->getJson('/api/v1/workers?verified=1')->assertOk();

        foreach ($res->json('data') as $w) {
            $this->assertTrue($w['is_verified']);
        }
    }

    public function test_offres_publiees_filtrees_par_ville(): void
    {
        $res = $this->getJson('/api/v1/offers?city=Yaoundé')->assertOk();

        $this->assertNotEmpty($res->json('data'));
        foreach ($res->json('data') as $o) {
            $this->assertSame('Yaoundé', $o['city']);
            $this->assertSame('published', $o['status']);
        }
    }
}
