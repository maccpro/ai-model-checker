<?php

namespace Tests\Feature;

use App\Models\BenchmarkRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_strength_suites(): void
    {
        $response = $this->getJson('/api/benchmark/suites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'suites' => [
                    'reasoning_math',
                    'code_algorithm',
                    'strict_constraints',
                    'json_schema',
                    'speed_throughput',
                ],
            ]);
    }

    public function test_it_executes_benchmark_and_records_results(): void
    {
        Http::fake([
            'https://fake-ai.test/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-test',
                'object' => 'chat.completion',
                'created' => 1700000000,
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Sally has 1 sister.',
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 25,
                    'completion_tokens' => 12,
                    'total_tokens' => 37,
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/benchmark/run', [
            'base_url' => 'https://fake-ai.test/v1',
            'api_key' => 'sk-test',
            'models' => ['gpt-4o-mini'],
            'prompt' => 'How many sisters does Sally have?',
            'mode' => 'playground',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'mode' => 'playground',
                    'results' => [
                        [
                            'model_name' => 'gpt-4o-mini',
                            'success' => true,
                            'content' => 'Sally has 1 sister.',
                            'prompt_tokens' => 25,
                            'completion_tokens' => 12,
                            'total_tokens' => 37,
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('benchmark_runs', [
            'prompt' => 'How many sisters does Sally have?',
        ]);

        $this->assertDatabaseHas('benchmark_results', [
            'model_name' => 'gpt-4o-mini',
            'status' => 'success',
        ]);
    }

    public function test_it_can_fetch_and_clear_history(): void
    {
        $run = BenchmarkRun::create([
            'mode' => 'playground',
            'prompt' => 'Test Prompt',
        ]);

        $response = $this->getJson('/api/benchmark/history');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Clear history
        $clearResponse = $this->deleteJson('/api/benchmark/history');
        $clearResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('benchmark_runs', ['id' => $run->id]);
    }
}
