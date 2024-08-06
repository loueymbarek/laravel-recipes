<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize all requests for now. You can add custom authorization logic here.
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:255',
            'ingredients' => 'sometimes|required|array',
            'steps' => 'sometimes|required|array',
            'category' => 'sometimes|required|string',
            'preparation_time' => 'sometimes|required|integer|min:1',
        ];
    }
}
