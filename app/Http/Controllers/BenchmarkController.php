<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunBenchmarkRequest;
use App\Services\AiClientService;
use App\Services\BenchmarkService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BenchmarkController extends Controller
{
    public function __construct(
        protected BenchmarkService $benchmarkService,
        protected AiClientService $aiClient
    ) {}

    /**
     * Run single prompt or comparative benchmark across selected models.
     */
    public function runBenchmark(RunBenchmarkRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->benchmarkService->executeBenchmark($validated);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Stream single model prompt completion via SSE.
     */
    public function streamPrompt(RunBenchmarkRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $firstModel = $validated['models'][0] ?? '';

        return $this->aiClient->streamChatCompletion(
            $validated['base_url'],
            $validated['api_key'] ?? null,
            [
                'model' => $firstModel,
                'prompt' => $validated['prompt'],
                'system_prompt' => $validated['system_prompt'] ?? null,
                'temperature' => $validated['temperature'] ?? 0.7,
                'max_tokens' => $validated['max_tokens'] ?? null,
                'top_p' => $validated['top_p'] ?? 1.0,
            ]
        );
    }

    /**
     * Get pre-configured strength benchmark suites.
     */
    public function getStrengthSuites(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'suites' => $this->benchmarkService->getStrengthSuites(),
        ]);
    }

    /**
     * Get past benchmark runs history.
     */
    public function getHistory(): JsonResponse
    {
        $runs = $this->benchmarkService->getRecentRuns(30);

        return response()->json([
            'success' => true,
            'runs' => $runs,
        ]);
    }

    /**
     * Delete a single benchmark run.
     */
    public function deleteRun(int $id): JsonResponse
    {
        $this->benchmarkService->deleteRun($id);

        return response()->json([
            'success' => true,
            'message' => 'Benchmark run deleted.',
        ]);
    }

    /**
     * Clear all benchmark history.
     */
    public function clearHistory(): JsonResponse
    {
        $this->benchmarkService->clearAllHistory();

        return response()->json([
            'success' => true,
            'message' => 'All benchmark history cleared.',
        ]);
    }
}
