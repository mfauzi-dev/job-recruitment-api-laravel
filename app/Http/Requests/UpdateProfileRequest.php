<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(auth()->id())],
            'curriculum_vitae_url' => ['nullable', 'file', 'mimes:pdf', 'max:5120'], // max 5MB
        ];
    }

    public function messages()
    {
        return [
            'curriculum_vitae_url.mimes' => 'CV must be a PDF file.',
            'curriculum_vitae_url.max' => 'CV size must not exceed 5MB.',
        ];
    }
}