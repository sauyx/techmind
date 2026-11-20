<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'description' => ['nullable', 'string'],
            'development_hint' => ['nullable', 'string', 'max:150'],
            'weight' => ['required', 'numeric', 'min:0.5', 'max:2.0'],
            'step_group' => ['required', 'integer', 'min:1', 'max:3'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
