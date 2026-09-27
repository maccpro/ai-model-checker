<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'benchmark_run_id',
        'model_name',
        'base_url',
        'response_content',
        'ttft_ms',
        'total_duration_ms',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'tokens_per_second',
        'status',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ttft_ms' => 'integer',
            'total_duration_ms' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'tokens_per_second' => 'float',
        ];
    }

    /**
     * Get the benchmark run that owns the result.
     *
     * @return BelongsTo<BenchmarkRun, $this>
     */
    public function benchmarkRun(): BelongsTo
    {
        return $this->belongsTo(BenchmarkRun::class);
    }
}
