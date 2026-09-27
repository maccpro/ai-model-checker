<?php

namespace App\Services;

use App\Models\SavedEndpoint;
use Illuminate\Database\Eloquent\Collection;

class EndpointService
{
    /**
     * Get all active saved endpoints.
     *
     * @return Collection<int, SavedEndpoint>
     */
    public function getActiveEndpoints(): Collection
    {
        return SavedEndpoint::where('is_active', true)
            ->latest()
            ->get(['id', 'name', 'base_url', 'api_key', 'created_at']);
    }

    /**
     * Create a new saved endpoint.
     *
     * @param  array{name: string, base_url: string, api_key?: ?string}  $data
     */
    public function createEndpoint(array $data): SavedEndpoint
    {
        return SavedEndpoint::create($data);
    }

    /**
     * Delete a saved endpoint by ID.
     */
    public function deleteEndpoint(int $id): bool
    {
        return (bool) SavedEndpoint::where('id', $id)->delete();
    }
}
