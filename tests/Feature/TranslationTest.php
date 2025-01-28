<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_export_performance()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Seed data
        $this->artisan('translations:generate 5')->assertExitCode(0);

        $start = microtime(true);
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/export');
        $time = microtime(true) - $start;

        $response->assertOk();
        $this->assertLessThan(0.5, $time, 'Export response time exceeded 500ms');
    }
}
