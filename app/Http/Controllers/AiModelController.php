<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchModelsRequest;
use App\Http\Requests\SaveEndpointRequest;
use App\Services\AiClientService;
use App\Services\EndpointService;
use Illuminate\Http\JsonResponse;

class AiModelController extends Controller
{
    public function __construct(
        protected AiClientService $aiClient,
        protected EndpointService $endpointService
    ) {}

    /**
     * Fetch models from the OpenAI-compatible endpoint.
     */
    public function fetchModels(FetchModelsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->aiClient->fetchModels(
            $validated['base_url'],
            $validated['api_key'] ?? null
        );

        $status = $result['success'] ? 200 : ($result['http_status'] ?? 500);

        return response()->json($result, $status >= 200 && $status < 600 ? $status : 500);
    }

    /**
     * List user-saved custom endpoints.
     */
    public function getSavedEndpoints(): JsonResponse
    {
        $endpoints = $this->endpointService->getActiveEndpoints();

        // Mask the api_key for security in UI
        $masked = $endpoints->map(function ($item) {
            $key = $item->api_key;
            $maskedKey = null;
            if (! empty($key)) {
                $len = strlen($key);
                $maskedKey = $len > 8
                    ? substr($key, 0, 4).str_repeat('•', max($len - 8, 4)).substr($key, -4)
                    : '••••••••';
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'base_url' => $item->base_url,
                'has_api_key' => ! empty($item->api_key),
                'masked_api_key' => $maskedKey,
                'raw_api_key' => $item->api_key,
            ];
        });

        return response()->json([
            'success' => true,
            'endpoints' => $masked,
        ]);
    }

    /**
     * Save a custom endpoint for future usage.
     */
    public function saveEndpoint(SaveEndpointRequest $request): JsonResponse
    {
        $endpoint = $this->endpointService->createEndpoint($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Endpoint saved successfully.',
            'endpoint' => [
                'id' => $endpoint->id,
                'name' => $endpoint->name,
                'base_url' => $endpoint->base_url,
                'has_api_key' => ! empty($endpoint->api_key),
                'raw_api_key' => $endpoint->api_key,
            ],
        ], 201);
    }

    /**
     * Delete a saved custom endpoint.
     */
    public function deleteEndpoint(int $id): JsonResponse
    {
        $this->endpointService->deleteEndpoint($id);

        return response()->json([
            'success' => true,
            'message' => 'Endpoint removed successfully.',
        ]);
    }
}
