<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'location' => ['sometimes', 'string', 'max:255'],
            'job_type' => ['nullable', 'in:full-time,part-time,contract,internship'],
            'status' => ['sometimes', 'in:open,closed'],
            'deadline' => ['nullable', 'date'],
        ];
    }

    public function messages()
    {
        return [
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
        ];
    }
}