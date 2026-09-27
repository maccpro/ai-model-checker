<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RunBenchmarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'base_url' => ['required', 'string', 'url'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'models' => ['required', 'array', 'min:1'],
            'models.*' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string', 'max:100000'],
            'system_prompt' => ['nullable', 'string', 'max:50000'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'mode' => ['nullable', 'string', 'in:playground,arena,strength_suite'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
