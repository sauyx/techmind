<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'suggestion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
