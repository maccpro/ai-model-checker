<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BenchmarkRun extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mode',
        'category',
        'prompt',
        'system_prompt',
        'parameters',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parameters' => 'array',
        ];
    }

    /**
     * Get the benchmark results for this run.
     *
     * @return HasMany<BenchmarkResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(BenchmarkResult::class);
    }
}
