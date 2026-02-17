<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'job_id' => ['required', 'exists:jobs,id'],
            'cover_letter_url' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // max 5MB
        ];
    }
}