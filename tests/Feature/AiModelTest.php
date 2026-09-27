<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_base_url_when_fetching_models(): void
    {
        $response = $this->postJson('/api/models/fetch', [
            'base_url' => 'not-a-valid-url',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['base_url']);
    }

    public function test_it_fetches_and_sorts_models_successfully(): void
    {
        Http::fake([
            'https://fake-ai.test/v1/models' => Http::response([
                'object' => 'list',
                'data' => [
                    ['id' => 'model-beta', 'created' => 1700000000, 'owned_by' => 'org'],
                    ['id' => 'model-alpha', 'created' => 1700000001, 'owned_by' => 'org'],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/models/fetch', [
            'base_url' => 'https://fake-ai.test/v1',
            'api_key' => 'test-key',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'models' => [
                    ['id' => 'model-alpha'],
                    ['id' => 'model-beta'],
                ],
            ]);
    }

    public function test_it_can_create_list_and_delete_saved_endpoints(): void
    {
        // 1. Create
        $createResponse = $this->postJson('/api/endpoints', [
            'name' => 'Production Groq',
            'base_url' => 'https://api.groq.com/openai/v1',
            'api_key' => 'gsk_test12345678',
        ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'endpoint' => [
                    'name' => 'Production Groq',
                    'base_url' => 'https://api.groq.com/openai/v1',
                ],
            ]);

        $endpointId = $createResponse->json('endpoint.id');
        $this->assertDatabaseHas('saved_endpoints', ['id' => $endpointId]);

        // 2. List
        $listResponse = $this->getJson('/api/endpoints');
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'endpoints']);

        // 3. Delete
        $deleteResponse = $this->deleteJson("/api/endpoints/{$endpointId}");
        $deleteResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('saved_endpoints', ['id' => $endpointId]);
    }
}
