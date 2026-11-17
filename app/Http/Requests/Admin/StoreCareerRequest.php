<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:96', 'unique:careers,code'],
            'name' => ['required', 'string', 'max:128'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['nullable', 'string', 'max:255'],
            'tools' => ['nullable', 'array'],
            'tools.*' => ['nullable', 'string', 'max:255'],
            'roadmap' => ['nullable', 'array'],
            'roadmap.*' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_validated' => ['required', 'boolean'],
            'source_note' => ['nullable', 'string', 'max:255'],
            'profiles' => ['required', 'array'],
            'profiles.*' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
