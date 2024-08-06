<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize all requests for now. You can add custom authorization logic here.
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'ingredients' => 'required|array',
            'steps' => 'required|array',
            'category' => 'required|string',
            'preparation_time' => 'required|integer|min:1',
        ];
    }
}
