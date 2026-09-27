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
     * Get categorized demo prompts for system prompts, user prompts, and paired scenarios.
     *
     * @return array{
     *     system_prompts: list<array{id: string, label: string, icon: string, prompt: string}>,
     *     user_prompts: list<array{id: string, label: string, icon: string, prompt: string, category: string}>,
     *     paired_scenarios: list<array{id: string, label: string, icon: string, system_prompt: string, prompt: string}>
     * }
     */
    public function getDemoPrompts(): array
    {
        return [
            'system_prompts' => [
                [
                    'id' => 'senior_architect',
                    'label' => 'Senior Architect',
                    'icon' => 'code',
                    'prompt' => 'You are an expert Principal Software Engineer. Write clean, idiomatic, type-hinted code with optimal time and space complexity, following SOLID principles and zero duplicate logic.',
                ],
                [
                    'id' => 'logic_tutor',
                    'label' => 'Logic Tutor',
                    'icon' => 'brain',
                    'prompt' => 'You are a rigorous analytical logic assistant. Break down problems systematically, evaluate premises, and provide clear step-by-step mathematical reasoning.',
                ],
                [
                    'id' => 'strict_json',
                    'label' => 'Strict JSON Bot',
                    'icon' => 'database',
                    'prompt' => 'You are an automated API parser. Return ONLY valid parseable JSON adhering strictly to the requested schema. Do not output markdown code fences, backticks, conversational preamble, or explanations.',
                ],
                [
                    'id' => 'bangla_expert',
                    'label' => 'Bangla Linguist',
                    'icon' => 'languages',
                    'prompt' => 'আপনি একজন প্রফেশনাল বাংলা ভাষা বিশেষজ্ঞ ও অনুবাদক। টেকনিক্যাল ও কম্পিউটার সায়েন্সের জটিল বিষয়বস্তু সহজ, স্বাভাবিক এবং মানসম্মত প্রাতিষ্ঠানিক বাংলায় ব্যাখ্যা ও অনুবাদ করুন।',
                ],
                [
                    'id' => 'security_auditor',
                    'label' => 'Security Auditor',
                    'icon' => 'shield-alert',
                    'prompt' => 'You are a seasoned Application Security (AppSec) auditor. Analyze code and architecture for vulnerabilities, injection vectors, OWASP Top 10 risks, and suggest secure parameterized remediation.',
                ],
                [
                    'id' => 'concise_minimal',
                    'label' => 'Minimalist',
                    'icon' => 'zap',
                    'prompt' => 'Be extremely concise and direct. Deliver the exact answer or code required with zero introductory remarks, fluff, or polite conversational fillers.',
                ],
            ],
            'user_prompts' => [
                [
                    'id' => 'logic_sally',
                    'label' => 'Sally\'s Sisters',
                    'icon' => 'help-circle',
                    'category' => 'Logic',
                    'prompt' => 'Sally has 3 brothers. Each brother has 2 sisters. How many sisters does Sally have in total? Explain your deduction step by step.',
                ],
                [
                    'id' => 'code_palindrome',
                    'label' => 'PHP Algorithm',
                    'icon' => 'terminal',
                    'category' => 'Coding',
                    'prompt' => 'Write a clean PHP 8.5 function `longestPalindrome(string $s): string` that finds the longest palindromic substring in O(n^2) or better time complexity. Include strict types, docblock, and handle multibyte UTF-8 characters safely.',
                ],
                [
                    'id' => 'constraint_lipogram',
                    'label' => 'No "E" Lipogram',
                    'icon' => 'ban',
                    'category' => 'Constraint',
                    'prompt' => 'Write a short 3-sentence paragraph describing the ocean without using the letter "e" (uppercase or lowercase) anywhere in your entire response. Count each sentence.',
                ],
                [
                    'id' => 'json_schema_eval',
                    'label' => 'JSON Extraction',
                    'icon' => 'braces',
                    'category' => 'JSON',
                    'prompt' => 'Produce a valid JSON object with the following exact keys: "benchmark_version" (string "1.0"), "model_capabilities" (array of 3 distinct strings), and "metrics" (object containing "tps": float, "reliability_score": integer between 1 and 100).',
                ],
                [
                    'id' => 'speed_count',
                    'label' => 'Speed Benchmark',
                    'icon' => 'gauge',
                    'category' => 'Speed',
                    'prompt' => 'Count from 1 to 100 separated by single spaces. Output only the numbers, nothing else.',
                ],
                [
                    'id' => 'bangla_translation',
                    'label' => 'Bangla Translation',
                    'icon' => 'book-open',
                    'category' => 'Language',
                    'prompt' => 'Translate this technical architecture sentence into fluent, professional Bengali (বাংলা): "Zero-downtime rolling deployment with database lock mitigation ensures uninterrupted enterprise SaaS operations."',
                ],
                [
                    'id' => 'security_audit_sql',
                    'label' => 'SQL Injection Audit',
                    'icon' => 'shield-check',
                    'category' => 'Security',
                    'prompt' => 'Audit this PHP query for SQL injection vulnerabilities and provide the secure refactored code using Laravel Eloquent PDO parameter bindings: `SELECT * FROM users WHERE email = \'$email\' AND active = 1`',
                ],
            ],
            'paired_scenarios' => [
                [
                    'id' => 'scenario_code',
                    'label' => 'Code Optimization & Review',
                    'icon' => 'cpu',
                    'system_prompt' => 'You are an expert Principal Software Engineer. Write clean, idiomatic, type-hinted code with optimal time and space complexity, following SOLID principles and zero duplicate logic.',
                    'prompt' => 'Write a clean PHP 8.5 function `longestPalindrome(string $s): string` that finds the longest palindromic substring in O(n^2) or better time complexity. Include strict types, docblock, and handle multibyte UTF-8 characters safely.',
                ],
                [
                    'id' => 'scenario_logic',
                    'label' => 'Multi-step Logic Puzzle',
                    'icon' => 'puzzle',
                    'system_prompt' => 'You are a rigorous analytical logic assistant. Break down problems systematically, evaluate premises, and provide clear step-by-step mathematical reasoning.',
                    'prompt' => 'Sally has 3 brothers. Each brother has 2 sisters. How many sisters does Sally have in total? Explain your deduction step by step.',
                ],
                [
                    'id' => 'scenario_json',
                    'label' => 'Strict JSON API Response',
                    'icon' => 'file-code',
                    'system_prompt' => 'You are an automated API parser. Return ONLY valid parseable JSON adhering strictly to the requested schema. Do not output markdown code fences, backticks, conversational preamble, or explanations.',
                    'prompt' => 'Produce a valid JSON object with the following exact keys: "benchmark_version" (string "1.0"), "model_capabilities" (array of 3 distinct strings), and "metrics" (object containing "tps": float, "reliability_score": integer between 1 and 100).',
                ],
                [
                    'id' => 'scenario_bangla',
                    'label' => 'Bengali Technical Localization',
                    'icon' => 'languages',
                    'system_prompt' => 'আপনি একজন প্রফেশনাল বাংলা ভাষা বিশেষজ্ঞ ও অনুবাদক। টেকনিক্যাল ও কম্পিউটার সায়েন্সের জটিল বিষয়বস্তু সহজ, স্বাভাবিক এবং মানসম্মত প্রাতিষ্ঠানিক বাংলায় ব্যাখ্যা ও অনুবাদ করুন।',
                    'prompt' => 'Translate this technical architecture sentence into fluent, professional Bengali (বাংলা): "Zero-downtime rolling deployment with database lock mitigation ensures uninterrupted enterprise SaaS operations."',
                ],
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
