<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize()
    {
        // Only company can update application status
        return true;
    }

    public function rules()
    {
        return [
            'status' => ['required', 'in:pending,accepted,rejected'],
        ];
    }
}