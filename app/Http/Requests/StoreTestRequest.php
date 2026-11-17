<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name' => ['nullable', 'string', 'max:128'],
            'ratings' => ['required', 'array'],
            'ratings.*' => ['integer', 'between:1,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'ratings.required' => 'Semua jawaban wajib diisi.',
            'ratings.array' => 'Format jawaban tidak valid.',
            'ratings.*.integer' => 'Setiap jawaban harus berupa angka bulat.',
            'ratings.*.between' => 'Setiap jawaban harus berada pada skala 1 sampai 5.',
        ];
    }
}
