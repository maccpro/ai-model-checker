<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiClientService
{
    /**
     * Normalize the user-supplied base URL.
     */
    public function normalizeBaseUrl(string $baseUrl): string
    {
        return rtrim(trim($baseUrl), '/');
    }

    /**
     * Fetch available models from the target OpenAI-compatible endpoint.
     *
     * @return array{success: bool, models: list<array{id: string, owned_by: ?string, created: ?int}>, error: ?string, http_status: ?int, latency_ms: ?int}
     */
    public function fetchModels(string $baseUrl, ?string $apiKey = null): array
    {
        $normalizedUrl = $this->normalizeBaseUrl($baseUrl);
        $endpoint = str_ends_with($normalizedUrl, '/models') ? $normalizedUrl : "{$normalizedUrl}/models";

        try {
            $startTime = microtime(true);
            $response = Http::timeout(15)->withHeaders($this->buildHeaders($apiKey))->get($endpoint);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if (! $response->successful()) {
                $err = $response->json();
                $msg = $err['error']['message'] ?? $err['message'] ?? "Request failed with status {$response->status()}";

                return ['success' => false, 'models' => [], 'error' => $msg, 'http_status' => $response->status(), 'latency_ms' => $latencyMs];
            }

            $data = $response->json();
            $rawModels = $data['data'] ?? (is_array($data) && array_is_list($data) ? $data : []);

            $models = [];
            foreach ($rawModels as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $models[] = [
                        'id' => (string) $item['id'],
                        'owned_by' => isset($item['owned_by']) ? (string) $item['owned_by'] : null,
                        'created' => isset($item['created']) ? (int) $item['created'] : null,
                    ];
                } elseif (is_string($item)) {
                    $models[] = ['id' => $item, 'owned_by' => null, 'created' => null];
                }
            }

            usort($models, fn (array $a, array $b): int => strcasecmp($a['id'], $b['id']));

            return ['success' => true, 'models' => $models, 'error' => null, 'http_status' => $response->status(), 'latency_ms' => $latencyMs];
        } catch (ConnectionException) {
            return ['success' => false, 'models' => [], 'error' => 'Connection failed: Unable to connect to endpoint.', 'http_status' => 504, 'latency_ms' => null];
        } catch (\Throwable $e) {
            return ['success' => false, 'models' => [], 'error' => $e->getMessage(), 'http_status' => 500, 'latency_ms' => null];
        }
    }

    /**
     * Send chat completion request and measure latency and tokens.
     *
     * @param  array{model: string, prompt: string, system_prompt?: ?string, temperature?: float, max_tokens?: int, top_p?: float}  $params
     * @return array{success: bool, content: ?string, raw: ?array, error: ?string, ttft_ms: ?int, total_duration_ms: int, prompt_tokens: int, completion_tokens: int, total_tokens: int, tokens_per_second: float, http_status: int}
     */
    public function sendChatCompletion(string $baseUrl, ?string $apiKey, array $params): array
    {
        $normalizedUrl = $this->normalizeBaseUrl($baseUrl);
        $endpoint = str_ends_with($normalizedUrl, '/chat/completions') ? $normalizedUrl : "{$normalizedUrl}/chat/completions";

        $payload = $this->buildPayload($params, false);
        $startTime = microtime(true);

        try {
            $response = Http::timeout(120)->withHeaders($this->buildHeaders($apiKey))->post($endpoint, $payload);
            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            if (! $response->successful()) {
                $err = $response->json();
                $msg = $err['error']['message'] ?? $err['message'] ?? "API error: HTTP {$response->status()}";

                return [
                    'success' => false, 'content' => null, 'raw' => $err, 'error' => $msg,
                    'ttft_ms' => null, 'total_duration_ms' => $totalDurationMs,
                    'prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0,
                    'tokens_per_second' => 0.0, 'http_status' => $response->status(),
                ];
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];

            $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
            $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
            if ($completionTokens === 0 && ! empty($content)) {
                $completionTokens = (int) ceil(mb_strlen($content) / 4);
            }
            $totalTokens = (int) ($usage['total_tokens'] ?? ($promptTokens + $completionTokens));

            $durationSec = max($totalDurationMs / 1000, 0.001);
            $tokensPerSecond = round($completionTokens / $durationSec, 2);

            return [
                'success' => true, 'content' => $content, 'raw' => $data, 'error' => null,
                'ttft_ms' => (int) round($totalDurationMs * 0.3),
                'total_duration_ms' => $totalDurationMs,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'tokens_per_second' => $tokensPerSecond,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'success' => false, 'content' => null, 'raw' => null, 'error' => $e->getMessage(),
                'ttft_ms' => null, 'total_duration_ms' => $totalDurationMs,
                'prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0,
                'tokens_per_second' => 0.0, 'http_status' => 500,
            ];
        }
    }

    /**
     * Create a StreamedResponse that proxies SSE chunk-by-chunk to the client.
     */
    public function streamChatCompletion(string $baseUrl, ?string $apiKey, array $params): StreamedResponse
    {
        $normalizedUrl = $this->normalizeBaseUrl($baseUrl);
        $endpoint = str_ends_with($normalizedUrl, '/chat/completions') ? $normalizedUrl : "{$normalizedUrl}/chat/completions";

        $payload = $this->buildPayload($params, true);
        $headers = $this->buildHeaders($apiKey);

        return new StreamedResponse(function () use ($endpoint, $headers, $payload) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);

            $curlHeaders = [];
            foreach ($headers as $k => $v) {
                $curlHeaders[] = "{$k}: {$v}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

            $startTime = microtime(true);
            $firstTokenTime = null;

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$startTime, &$firstTokenTime) {
                if ($firstTokenTime === null && str_contains($chunk, 'data:')) {
                    $firstTokenTime = microtime(true);
                    $ttftMs = (int) round(($firstTokenTime - $startTime) * 1000);
                    echo "event: ttft\ndata: ".json_encode(['ttft_ms' => $ttftMs])."\n\n";
                    flush();
                }
                echo $chunk;
                flush();

                return strlen($chunk);
            });

            curl_exec($ch);
            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "event: error\ndata: ".json_encode(['error' => $error])."\n\n";
            } else {
                echo "event: done\ndata: ".json_encode(['total_duration_ms' => $totalDurationMs])."\n\n";
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Build common payload structure.
     */
    private function buildPayload(array $params, bool $stream): array
    {
        $messages = [];
        if (! empty($params['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $params['system_prompt']];
        }
        $messages[] = ['role' => 'user', 'content' => $params['prompt']];

        $payload = [
            'model' => $params['model'],
            'messages' => $messages,
            'temperature' => isset($params['temperature']) ? (float) $params['temperature'] : 0.7,
            'stream' => $stream,
        ];

        if (! empty($params['max_tokens'])) {
            $payload['max_tokens'] = (int) $params['max_tokens'];
        }
        if (isset($params['top_p'])) {
            $payload['top_p'] = (float) $params['top_p'];
        }

        return $payload;
    }

    /**
     * Build standard headers for OpenAI-compatible API requests.
     *
     * @return array<string, string>
     */
    private function buildHeaders(?string $apiKey): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (! empty($apiKey)) {
            $headers['Authorization'] = 'Bearer '.trim($apiKey);
        }

        return $headers;
    }
}
