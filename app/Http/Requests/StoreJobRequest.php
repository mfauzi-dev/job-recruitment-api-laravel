<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['nullable', 'in:full-time,part-time,contract,internship'],
            'deadline' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages()
    {
        return [
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'deadline.after' => 'Deadline must be a future date.',
        ];
    }
}