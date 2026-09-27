<?php

namespace App\Services;

use App\Models\BenchmarkResult;
use App\Models\BenchmarkRun;
use Illuminate\Database\Eloquent\Collection;

class BenchmarkService
{
    public function __construct(
        protected AiClientService $aiClient
    ) {}

    /**
     * Get predefined strength benchmark suites.
     *
     * @return array<string, array{
     *     name: string,
     *     description: string,
     *     category: string,
     *     system_prompt: ?string,
     *     prompt: string,
     *     expected_trait: string
     * }>
     */
    public function getStrengthSuites(): array
    {
        return [
            'reasoning_math' => [
                'name' => 'Multi-step Logic & Math',
                'category' => 'reasoning',
                'description' => 'Evaluates analytical reasoning, avoiding common cognitive traps and step-by-step arithmetic deduction.',
                'system_prompt' => 'You are a rigorous analytical assistant. Reason clearly and provide your final concise answer at the end.',
                'prompt' => 'Sally has 3 brothers. Each brother has 2 sisters. How many sisters does Sally have in total? Explain your deduction step by step.',
                'expected_trait' => 'Correctly identifies that all brothers share the same sisters, so Sally has 1 sister (2 sisters in family - Sally herself).',
            ],
            'code_algorithm' => [
                'name' => 'Algorithm & Code Quality',
                'category' => 'coding',
                'description' => 'Evaluates algorithmic efficiency, type safety, edge-case coverage, and clean PHP 8.5 syntax.',
                'system_prompt' => 'You are an expert Principal Software Engineer. Write clean, idiomatic, type-hinted code with optimal time and space complexity.',
                'prompt' => 'Write a clean PHP 8.5 function `longestPalindrome(string $s): string` that finds the longest palindromic substring in O(n^2) or better time complexity. Include strict types, docblock, and handle empty strings and multibyte characters safely.',
                'expected_trait' => 'Provides clean expand-around-center or Manachers algorithm with PHP 8.5 strict types and multibyte handling.',
            ],
            'strict_constraints' => [
                'name' => 'Negative Constraints & Instruction Adherence',
                'category' => 'instructions',
                'description' => 'Evaluates strict adherence to negative rules (e.g. Lipogram - omitting letter "e") and structural constraints.',
                'system_prompt' => 'Follow all instructions strictly. Any deviation or violation of constraints is considered a complete failure.',
                'prompt' => 'Write a short 3-sentence paragraph describing the ocean without using the letter "e" (uppercase or lowercase) anywhere in your entire response. Count each sentence.',
                'expected_trait' => 'Zero occurrences of the letter "e" or "E" with exactly 3 grammatically sound sentences.',
            ],
            'json_schema' => [
                'name' => 'Strict JSON Output Compliance',
                'category' => 'formatting',
                'description' => 'Evaluates capability to output 100% valid JSON without markdown wrapping or conversational chit-chat.',
                'system_prompt' => 'You are an automated API processor. You MUST return ONLY valid JSON adhering exactly to the requested schema. No markdown fences, no explanations, no prefix or suffix.',
                'prompt' => 'Produce a valid JSON object with the following exact keys: "benchmark_version" (string "1.0"), "model_capabilities" (array of 3 distinct strings), and "metrics" (object containing "tps": float, "reliability_score": integer between 1 and 100).',
                'expected_trait' => 'Valid, parseable JSON with exact types and no extraneous characters or backticks.',
            ],
            'speed_throughput' => [
                'name' => 'Speed & Latency Throughput',
                'category' => 'speed',
                'description' => 'Measures raw generation throughput (tokens/second) on a sequential list output.',
                'system_prompt' => 'Output the requested sequence immediately with zero explanation or chatter.',
                'prompt' => 'Count from 1 to 100 separated by single spaces. Output only the numbers, nothing else.',
                'expected_trait' => 'Immediate TTFT and maximum sustained tokens per second output.',
            ],
        ];
    }

    /**
     * Run a benchmark test for a single model or multiple models.
     *
     * @param  array{
     *     base_url: string,
     *     api_key: ?string,
     *     models: list<string>,
     *     prompt: string,
     *     system_prompt?: ?string,
     *     temperature?: float,
     *     max_tokens?: int,
     *     top_p?: float,
     *     mode?: string,
     *     category?: ?string
     * }  $payload
     * @return array{
     *     run_id: int,
     *     mode: string,
     *     results: list<array{
     *         model_name: string,
     *         success: bool,
     *         content: ?string,
     *         ttft_ms: ?int,
     *         total_duration_ms: int,
     *         prompt_tokens: int,
     *         completion_tokens: int,
     *         total_tokens: int,
     *         tokens_per_second: float,
     *         error: ?string,
     *         http_status: int
     *     }>
     * }
     */
    public function executeBenchmark(array $payload): array
    {
        $mode = $payload['mode'] ?? 'playground';
        $category = $payload['category'] ?? null;

        // Create benchmark run record
        $benchmarkRun = BenchmarkRun::create([
            'mode' => $mode,
            'category' => $category,
            'prompt' => $payload['prompt'],
            'system_prompt' => $payload['system_prompt'] ?? null,
            'parameters' => [
                'temperature' => $payload['temperature'] ?? 0.7,
                'max_tokens' => $payload['max_tokens'] ?? null,
                'top_p' => $payload['top_p'] ?? 1.0,
            ],
        ]);

        $results = [];

        foreach ($payload['models'] as $modelName) {
            $completion = $this->aiClient->sendChatCompletion(
                $payload['base_url'],
                $payload['api_key'] ?? null,
                [
                    'model' => $modelName,
                    'prompt' => $payload['prompt'],
                    'system_prompt' => $payload['system_prompt'] ?? null,
                    'temperature' => $payload['temperature'] ?? 0.7,
                    'max_tokens' => $payload['max_tokens'] ?? null,
                    'top_p' => $payload['top_p'] ?? 1.0,
                ]
            );

            // Record result in database
            BenchmarkResult::create([
                'benchmark_run_id' => $benchmarkRun->id,
                'model_name' => $modelName,
                'base_url' => $payload['base_url'],
                'response_content' => $completion['content'],
                'ttft_ms' => $completion['ttft_ms'],
                'total_duration_ms' => $completion['total_duration_ms'],
                'prompt_tokens' => $completion['prompt_tokens'],
                'completion_tokens' => $completion['completion_tokens'],
                'total_tokens' => $completion['total_tokens'],
                'tokens_per_second' => $completion['tokens_per_second'],
                'status' => $completion['success'] ? 'success' : 'failed',
                'error_message' => $completion['error'],
            ]);

            $results[] = [
                'model_name' => $modelName,
                'success' => $completion['success'],
                'content' => $completion['content'],
                'ttft_ms' => $completion['ttft_ms'],
                'total_duration_ms' => $completion['total_duration_ms'],
                'prompt_tokens' => $completion['prompt_tokens'],
                'completion_tokens' => $completion['completion_tokens'],
                'total_tokens' => $completion['total_tokens'],
                'tokens_per_second' => $completion['tokens_per_second'],
                'error' => $completion['error'],
                'http_status' => $completion['http_status'],
            ];
        }

        return [
            'run_id' => $benchmarkRun->id,
            'mode' => $mode,
            'results' => $results,
        ];
    }

    /**
     * Get recent benchmark runs with their results.
     *
     * @return Collection<int, BenchmarkRun>
     */
    public function getRecentRuns(int $limit = 20): Collection
    {
        return BenchmarkRun::with('results')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Delete a single benchmark run by ID.
     */
    public function deleteRun(int $runId): bool
    {
        return (bool) BenchmarkRun::where('id', $runId)->delete();
    }

    /**
     * Delete all benchmark runs history.
     */
    public function clearAllHistory(): void
    {
        BenchmarkRun::query()->delete();
    }
}
